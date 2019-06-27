<?php
namespace php_ext;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

/**
 * 文件操作类
 * Class FileUtils
 *
 * @package php_ext
 */
class FileUtils
{
    /**
     * 创建多级目录
     *
     * @param string $dir  文件夹路径
     * @param int    $mode 权限
     *
     * @return boolean
     */
    public static function createDir($dir, $mode = 0777)
    {
        $dir = self::dirReplace($dir);
        return is_dir($dir) or (self::createDir(dirname($dir)) and mkdir($dir, $mode));
    }

    /**
     * 创建指定路径下的指定文件
     *
     * @param string  $path       (需要包含文件名和后缀)
     * @param boolean $over_write 是否覆盖文件
     * @param int     $time       设置时间。默认是当前系统时间
     * @param int     $atime      设置访问时间。默认是当前系统时间
     *
     * @return boolean
     */
    public static function createFile($path, $over_write = false, $time = null, $atime = null)
    {
        $path = self::dirReplace($path);
        $time = empty($time) ? time() : $time;
        $atime = empty($atime) ? time() : $atime;
        if (file_exists($path) && $over_write) {
            self::unlinkFile($path);
        }
        $aimDir = dirname($path);
        self::createDir($aimDir);
        return touch($path, $time, $atime);
    }

    /**
     * 关闭文件操作
     *
     * @param resource $path 文件资源
     *
     * @return boolean
     */
    public static function close($path)
    {
        return fclose($path);
    }

    /**
     * 确定服务器的最大上传限制（字节数）
     *
     * @return int 服务器允许的最大上传字节数
     */
    public static function allowUploadSize()
    {
        $val = trim(ini_get('upload_max_filesize'));
        return $val;
    }

    /**
     * 是否文件存在
     *
     * @param string $path 文件路径
     *
     * @return bool
     */
    public static function fileExists($path)
    {
        $path = self::dirReplace($path);
        return is_file($path);
    }

    /**
     * 是否文件夹存在
     *
     * @param string $path 文件夹路径
     *
     * @return bool
     */
    public static function dirExists($path)
    {
        $path = self::dirReplace($path);
        return is_dir($path);
    }

    /**
     * 字节格式化 把字节数格式为 B K M G T P E Z Y 描述的大小
     *
     * @param int $size 大小
     * @param int $dec  显示类型
     *
     * @return int
     */
    public static function byteFormat($size, $dec = 2)
    {
        $a = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
        $pos = 0;
        while ($size >= 1024) {
            $size /= 1024;
            $pos++;
        }
        return round($size, $dec) . " " . $a[$pos];
    }

    /**
     * 删除非空目录
     * 说明:只能删除非系统和特定权限的文件,否则会出现错误
     *
     * @param string  $dir_path 目录路径
     * @param boolean $is_all   是否删除所有
     *
     * @return boolean
     */
    public static function removeDir($dir_path, $is_all = false)
    {
        $dirName = self::dirReplace($dir_path);
        $handle = @opendir($dirName);
        while (($file = @readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                $dir = $dirName . DIRECTORY_SEPARATOR . $file;
                if ($is_all) {
                    is_dir($dir) ? self::removeDir($dir, $is_all) : self::unlinkFile($dir);
                } else {
                    if (is_file($dir)) {
                        self::unlinkFile($dir);
                    }
                }
            }
        }
        closedir($handle);
        return @rmdir($dirName);
    }

    /**
     * 获取完整文件名
     *
     * @param string $file_path 路径
     *
     * @return string
     */
    public static function getBaseName($file_path)
    {
        $file_path = self::dirReplace($file_path);
        return basename(self::dirReplace($file_path));
    }

    /**
     * 获取文件后缀名
     *
     * @param string $file_path 文件路径
     *
     * @return string
     */
    public static function getExt($file_path)
    {
        $file = self::dirReplace($file_path);
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * 取得指定目录名称
     *
     * @param string $path 文件路径
     * @param int    $num  需要返回以上级目录的数
     *
     * @return string
     */
    public static function fatherDir($path, $num = 1)
    {
        $path = self::dirReplace($path);
        $arr = explode(DIRECTORY_SEPARATOR, $path);
        if ($num == 0 || count($arr) < $num) {
            return pathinfo($path, PATHINFO_BASENAME);
        }
        return substr(strrev($path), 0, 1) == DIRECTORY_SEPARATOR ? $arr[(count($arr) - (1 + $num))] : $arr[(count($arr) - $num)];
    }

    /**
     * 删除文件
     *
     * @param string $path
     *
     * @return boolean
     */
    public static function unlinkFile($path)
    {
        $path = self::dirReplace($path);
        if (file_exists($path)) {
            return unlink($path);
        }
        return true;
    }

    /**
     * 文件操作(复制/移动)
     *
     * @param string  $old_path  指定要操作文件路径(需要含有文件名和后缀名)
     * @param string  $new_path  指定新文件路径（需要新的文件名和后缀名）
     * @param string  $type      文件操作类型
     * @param boolean $overWrite 是否覆盖已存在文件
     *
     * @return boolean
     */
    public static function handleFile($old_path, $new_path, $type = 'copy', $overWrite = false)
    {
        $old_path = self::dirReplace($old_path);
        $new_path = self::dirReplace($new_path);
        if (file_exists($new_path) && $overWrite == false) {
            return false;
        } else {
            if (file_exists($new_path) && $overWrite == true) {
                self::unlinkFile($new_path);
            }
        }
        $aimDir = dirname($new_path);
        self::createDir($aimDir);
        switch ($type) {
            case 'copy':
                return copy($old_path, $new_path);
                break;
            case 'move':
                return rename($old_path, $new_path);
                break;
        }
        return false;
    }

    /**
     * 文件夹操作(复制/移动)
     *
     * @param string  $old_path  指定要操作文件夹路径
     * @param string  $new_path  指定新文件夹路径
     * @param string  $type      操作类型
     * @param boolean $overWrite 是否覆盖文件和文件夹
     *
     * @return boolean
     */
    public static function handleDir($old_path, $new_path, $type = 'copy', $overWrite = false)
    {
        $new_path = self::checkPath($new_path);
        $old_path = self::checkPath($old_path);
        if (!is_dir($old_path)) {
            return false;
        }
        if (!file_exists($new_path)) {
            self::createDir($new_path);
        }
        $dirHandle = opendir($old_path);
        if (!$dirHandle) {
            return false;
        }
        $boolean = true;
        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($old_path . $file)) {
                $boolean = self::handleFile($old_path . $file, $new_path . $file, $type, $overWrite);
            } else {
                self::handleDir($old_path . $file, $new_path . $file, $type, $overWrite);
            }
        }
        switch ($type) {
            case 'copy':
                closedir($dirHandle);
                return $boolean;
                break;
            case 'move':
                closedir($dirHandle);
                return rmdir($old_path);
                break;
        }
        return false;
    }

    /**
     * 替换相应的字符
     *
     * @param string $path 路径
     *
     * @return string
     */
    public static function dirReplace($path)
    {
        if (DIRECTORY_SEPARATOR == '/') {
            $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
        } else {
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        }
        $path = str_replace('//', DIRECTORY_SEPARATOR, $path);
        $path = str_replace('\\\\', DIRECTORY_SEPARATOR, $path);
        return $path;
    }

    /**
     * 读取文件内容
     *
     * @param string $path 指定路径下的文件
     *
     * @return string
     */
    public static function readFile($path)
    {
        $path = self::dirReplace($path);
        if (file_exists($path)) {
            $fp = fopen($path, 'r');
            $rstr = fread($fp, filesize($path));
            fclose($fp);
            return $rstr;
        } else {
            return '';
        }
    }

    /**
     * 文件重命名
     *
     * @param string $oldName 旧文件名称
     * @param string $newName 新文件名称
     *
     * @return bool
     */
    public static function rename($oldName, $newName)
    {
        $oldName = self::dirReplace($oldName);
        $newName = self::dirReplace($newName);
        if (($newName != $oldName) && is_writable($oldName)) {
            return rename($oldName, $newName);
        }
        return false;
    }

    /**
     * 获取指定路径下的信息
     *
     * @param string $dir 路径
     *
     * @return array
     */
    public static function getDirInfo($dir)
    {
        $dir = self::dirReplace($dir);
        $handle = @opendir($dir);//打开指定目录
        $directory_count = $total_size = $file_count = 0;
        while (false !== ($file_path = readdir($handle))) {
            if ($file_path != "." && $file_path != "..") {
                $next_path = $dir . DIRECTORY_SEPARATOR . $file_path;
                if (is_dir($next_path)) {
                    $directory_count++;
                    $result_value = self::getDirInfo($next_path);
                    $total_size += $result_value['size'];
                    $file_count += $result_value['file_count'];
                    $directory_count += $result_value['dir_count'];
                } elseif (is_file($next_path)) {
                    $total_size += filesize($next_path);
                    $file_count++;
                }
            }
        }
        closedir($handle);//关闭指定目录
        $result_value['size'] = $total_size;
        $result_value['file_count'] = $file_count;
        $result_value['dir_count'] = $directory_count;
        return $result_value;
    }

    /**
     * 指定文件编码转换
     *
     * @param string $path       文件路径
     * @param string $input_code 原始编码
     * @param string $out_code   输出编码
     *
     * @return boolean
     */
    public static function changeFileCode($path, $input_code, $out_code)
    {
        $path = self::dirReplace($path);
        if (is_file($path)) {
            $content = file_get_contents($path);
            $content = self::changCode($content, $input_code, $out_code);
            $fp = fopen($path, 'w');
            $isOk = fputs($fp, $content) ? true : false;
            self::close($fp);
            return $isOk;
        }
        return false;
    }

    /**
     * 指定目录下指定条件文件编码转换
     *
     * @param string  $dirName    目录路径
     * @param string  $input_code 原始编码
     * @param string  $out_code   输出编码
     * @param boolean $is_all     是否转换所有子目录下文件编码
     * @param string  $exts       文件类型
     *
     * @return boolean
     */
    public static function changeDirFilesCode($dirName, $input_code, $out_code, $is_all = true, $exts = '')
    {
        $dirName = self::dirReplace($dirName);
        if (is_dir($dirName)) {
            $fh = opendir($dirName);
            while (($file = readdir($fh)) !== false) {
                if (strcmp($file, '.') == 0 || strcmp($file, '..') == 0) {
                    continue;
                }
                $filepath = $dirName . DIRECTORY_SEPARATOR . $file;
                if (is_dir($filepath) && $is_all == true) {
                    $files = self::changeDirFilesCode($filepath, $input_code, $out_code, $is_all, $exts);
                    if (!$files) {
                        return false;
                    }
                } else {
                    if (self::getExt($filepath) == $exts && is_file($filepath)) {
                        $boole = self::changeFileCode($filepath, $input_code, $out_code);
                        if (!$boole) {
                            continue;
                        }
                    }
                }
            }
            closedir($fh);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 列出指定目录下符合条件的文件和文件夹
     *
     * @param string  $dirName 路径
     * @param boolean $is_all  是否列出子目录中的文件
     * @param string  $exts    需要列出的后缀名文件
     * @param string  $sort    数组排序
     *
     * @return array|false
     */
    public static function listDirInfo($dirName, $is_all = false, $exts = '', $sort = 'ASC')
    {
        $dirName = self::dirReplace($dirName);
        //处理多于的/号
        $new = strrev($dirName);
        if (strpos($new, DIRECTORY_SEPARATOR) == 0) {
            $new = substr($new, 1);
        }
        $dirName = strrev($new);
        $sort = strtolower($sort);//将字符转换成小写
        $files = array();
        if (is_dir($dirName)) {
            $fh = opendir($dirName);
            while (($file = readdir($fh)) !== false) {
                if (strcmp($file, '.') == 0 || strcmp($file, '..') == 0) {
                    continue;
                }
                $filepath = $dirName . DIRECTORY_SEPARATOR . $file;
                switch ($exts) {
                    case '*':
                        if (is_dir($filepath) && $is_all == true) {
                            $files = array_merge($files, self::listDirInfo($filepath, $is_all, $exts, $sort));
                        }
                        array_push($files, $filepath);
                        break;
                    case 'folder':
                        if (is_dir($filepath) && $is_all == true) {
                            $files = array_merge($files, self::listDirInfo($filepath, $is_all, $exts, $sort));
                            array_push($files, $filepath);
                        } elseif (is_dir($filepath)) {
                            array_push($files, $filepath);
                        }
                        break;
                    case 'file':
                        if (is_dir($filepath) && $is_all == true) {
                            $files = array_merge($files, self::listDirInfo($filepath, $is_all, $exts, $sort));
                        } elseif (is_file($filepath)) {
                            array_push($files, $filepath);
                        }
                        break;
                    default:
                        if (is_dir($filepath) && $is_all == true) {
                            $files = array_merge($files, self::listDirInfo($filepath, $is_all, $exts, $sort));
                        } elseif (preg_match("/\.($exts)/i", $filepath) && is_file($filepath)) {
                            array_push($files, $filepath);
                        }
                        break;
                }
                switch ($sort) {
                    case 'asc':
                        sort($files);
                        break;
                    case 'desc':
                        rsort($files);
                        break;
                    case 'nat':
                        natcasesort($files);
                        break;
                }
            }
            closedir($fh);
            return $files;
        } else {
            return false;
        }
    }

    /**
     * 返回指定路径的文件夹信息，其中包含指定路径中的文件和目录
     *
     * @param string $dir
     *
     * @return array
     */
    public static function dirInfo($dir)
    {
        $dir = self::dirReplace($dir);
        return scandir($dir);
    }

    /**
     * 判断目录是否为空
     *
     * @param string $dir
     *
     * @return boolean
     */
    public static function isEmpty($dir)
    {
        $dir = self::dirReplace($dir);
        $handle = opendir($dir);
        while (($file = readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                closedir($handle);
                return true;
            }
        }
        closedir($handle);
        return false;
    }

    /**
     * 返回指定文件和目录的信息
     *
     * @param string $file
     *
     * @return array
     */
    public static function listInfo($file)
    {
        $file = self::dirReplace($file);
        $dir = array();
        $dir['filename'] = basename($file);//返回路径中的文件名部分。
        $dir['pathname'] = realpath($file);//返回绝对路径名。
        $dir['owner'] = fileowner($file);//文件的 user ID （所有者）。
        $dir['perms'] = fileperms($file);//返回文件的 inode 编号。
        $dir['inode'] = fileinode($file);//返回文件的 inode 编号。
        $dir['group'] = filegroup($file);//返回文件的组 ID。
        $dir['path'] = dirname($file);//返回路径中的目录名称部分。
        $dir['atime'] = fileatime($file);//返回文件的上次访问时间。
        $dir['ctime'] = filectime($file);//返回文件的上次改变时间。
        $dir['perms'] = fileperms($file);//返回文件的权限。
        $dir['size'] = filesize($file);//返回文件大小。
        $dir['type'] = filetype($file);//返回文件类型。
        $dir['ext'] = is_file($file) ? pathinfo($file, PATHINFO_EXTENSION) : '';//返回文件后缀名
        $dir['mtime'] = filemtime($file);//返回文件的上次修改时间。
        $dir['isDir'] = is_dir($file);//判断指定的文件名是否是一个目录。
        $dir['isFile'] = is_file($file);//判断指定文件是否为常规的文件。
        $dir['isLink'] = is_link($file);//判断指定的文件是否是连接。
        $dir['isReadable'] = is_readable($file);//判断文件是否可读。
        $dir['isWritable'] = is_writable($file);//判断文件是否可写。
        $dir['isUpload'] = is_uploaded_file($file);//判断文件是否是通过 HTTP POST 上传的。
        return $dir;
    }

    /**
     * 返回关于打开文件的信息
     *
     * @param $file
     *
     * @return array
     * 数字下标     关联键名（自 PHP 4.0.6）     说明
     * 0     dev     设备名
     * 1     ino     号码
     * 2     mode     inode 保护模式
     * 3     nlink     被连接数目
     * 4     uid     所有者的用户 id
     * 5     gid     所有者的组 id
     * 6     rdev     设备类型，如果是 inode 设备的话
     * 7     size     文件大小的字节数
     * 8     atime     上次访问时间（Unix 时间戳）
     * 9     mtime     上次修改时间（Unix 时间戳）
     * 10     ctime     上次改变时间（Unix 时间戳）
     * 11     blksize     文件系统 IO 的块大小
     * 12     blocks     所占据块的数目
     */
    public static function openInfo($file)
    {
        $file = self::dirReplace($file);
        $file = fopen($file, "r");
        $result = fstat($file);
        fclose($file);
        return $result;
    }

    /**
     * 改变文件和目录的相关属性
     *
     * @param string $file    文件路径
     * @param string $type    操作类型
     * @param string $ch_info 操作信息
     *
     * @return boolean
     */
    public static function changeFile($file, $type, $ch_info)
    {
        $file = self::dirReplace($file);
        $is_ok = false;
        switch ($type) {
            case 'group':
                $is_ok = chgrp($file, $ch_info);//改变文件组。
                break;
            case 'mode':
                $is_ok = chmod($file, $ch_info);//改变文件模式。
                break;
            case 'ower':
                $is_ok = chown($file, $ch_info);//改变文件所有者。
                break;
        }
        return $is_ok;
    }

    /**
     * 取得文件路径信息
     *
     * @param string $full_path 完整路径
     *
     * @return array
     */
    public static function getFileType($full_path)
    {
        $full_path = self::dirReplace($full_path);
        return pathinfo($full_path);
    }

    /**
     * 取得上传文件信息
     *
     * @param string $file file属性信息
     *
     * @return array
     */
    public static function getUploadFileInfo($file)
    {
        $file = self::dirReplace($file);
        $file_info = $_FILES[$file];//取得上传文件基本信息
        $info = array();
        $info['type'] = strtolower(trim(stripslashes(preg_replace("/^(.+?);.*$/", "\\1", $file_info['type'])), '"'));//取得文件类型
        $info['temp'] = $file_info['tmp_name'];//取得上传文件在服务器中临时保存目录
        $info['size'] = $file_info['size'];//取得上传文件大小
        $info['error'] = $file_info['error'];//取得文件上传错误
        $info['name'] = $file_info['name'];//取得上传文件名
        $info['ext'] = self::getExt($file_info['name']);//取得上传文件后缀
        return $info;
    }

    /**
     * 设置文件命名规则
     *
     * @param string $type 命名规则
     *
     * @return string
     */
    public static function setFileName($type)
    {
        switch ($type) {
            case 'hash':
                $new_file = md5(uniqid(mt_rand()));//mt_srand()以随机数md5加密来命名
                break;
            case 'time':
                $new_file = time();
                break;
            default:
                $new_file = date($type, time());//以时间格式来命名
                break;
        }
        return $new_file;
    }

    /**
     * 文件保存路径处理
     *
     * @param string $path 文件路径
     *
     * @return string
     */
    public static function checkPath($path)
    {
        $path = self::dirReplace($path);
        return (preg_match('/\/$/', $path)) ? $path : $path . DIRECTORY_SEPARATOR;
    }

    /**
     * 下载远程文件
     *
     * @param string $url      地址
     * @param string $save_dir 保存文件夹路径
     * @param string $filename 保存文件名称
     * @param int    $type     类型
     *
     * @return array
     */
    public static function downRemoteFile($url, $save_dir = '', $filename = '', $type = 0)
    {
        $save_dir = self::dirReplace($save_dir);
        if (trim($url) == '') {
            return array('file_name' => '', 'save_path' => '', 'error' => 1);
        }
        if (trim($save_dir) == '') {
            $save_dir = '.' . DIRECTORY_SEPARATOR;
        }
        if (trim($filename) == '') {//保存文件名
            $ext = strrchr($url, '.');
            $filename = time() . $ext;
        }
        if (0 !== strrpos($save_dir, DIRECTORY_SEPARATOR)) {
            $save_dir .= DIRECTORY_SEPARATOR;
        }
        //创建保存目录
        if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
            return array('file_name' => '', 'save_path' => '', 'error' => 5);
        }
        //获取远程文件所采用的方法
        if ($type) {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $img = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start();
            readfile($url);
            $img = ob_get_contents();
            ob_end_clean();
        }
        $size = strlen($img);
        //文件大小
        $fp2 = fopen($save_dir . $filename, 'a');
        fwrite($fp2, $img);
        fclose($fp2);
        unset($img, $url);
        return array('file_name' => $filename, 'save_path' => $save_dir . $filename, 'file_size' => $size,
            'error' => 0);
    }

    /**
     * 将字符串由$input_encoding转换为$output_encoding
     *
     * @param string $string          字符串
     * @param string $input_encoding  原始字符串编码
     * @param string $output_encoding 转换后字符串编码
     *
     * @return string
     */
    public static function changCode($string, $input_encoding = '', $output_encoding = '')
    {
        if (empty($input_encoding) || empty($output_encoding)) {
            return $string;
        }
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, $output_encoding, $input_encoding);
        } else {
            if (!function_exists('mb_convert_encoding') && function_exists('iconv')) {
                return iconv($input_encoding, $output_encoding, $string);
            } else {
                return $string;
            }
        }
    }

    /**
     * 读取文件内容
     *
     * @param string $file_path 文件路径
     *
     * @return bool|string
     */
    public static function readFileContent($file_path)
    {
        $file_path = self::dirReplace($file_path);
        if (self::fileExists($file_path)) {
            $info = file_get_contents($file_path);
            $info = self::clearBom($info);
            return $info;
        } else {
            return false;
        }
    }

    /**
     * 去掉文件中的 bom头
     *
     * @param string $contents 内容
     *
     * @return mixed
     */
    public static function clearBom($contents)
    {
        //UTF8 去掉文本中的 bom头
        $BOM = chr(239) . chr(187) . chr(191);
        return str_replace($BOM, '', $contents);
    }

    /**
     * 去掉文件中的bom头
     *
     * @param object $fileName 文件路径
     *
     * @return bool
     */
    public static function clearFileBom($fileName)
    {
        $fileName = self::dirReplace($fileName);
        if (self::fileExists($fileName)) {
            $c = file_get_contents($fileName);
            $c = self::clearBom($c);
            file_put_contents($fileName, $c);
        } else {
            return false;
        }
        return true;
    }

    /**
     * 递归打包文件夹
     *
     * @param string $source      原地址
     * @param string $destination 目标文件
     *
     * @return bool
     */
    public static function zip($source, $destination)
    {
        $source = self::dirReplace($source);
        $destination = self::dirReplace($destination);
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }
        $zip = new ZipArchive();
        if (!$zip->open($destination, ZipArchive::CREATE)) {
            return false;
        }
        $source = self::dirReplace(realpath($source));
        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($files as $file) {
                $file = self::dirReplace($file);
                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, DIRECTORY_SEPARATOR) + 1), array('.', '..'))) {
                    continue;
                }
                $file = realpath($file);
                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace(dirname($source) . DIRECTORY_SEPARATOR, '', $file . DIRECTORY_SEPARATOR));
                } else {
                    if (is_file($file) === true) {
                        $zip->addFromString(str_replace(dirname($source) . DIRECTORY_SEPARATOR, '', $file), file_get_contents($file));
                    }
                }
            }
        } else {
            if (is_file($source) === true) {
                $zip->addFromString(basename($source), file_get_contents($source));
            }
        }
        return $zip->close();
    }

    /**
     * 读取文件
     *
     * @param string $fileName 文件名称
     * @param string $key      关键字
     * @param int    $start    起始行
     * @param int    $limit    数量
     * @param bool   $revers   是否反向
     *
     * @return array|string
     */
    public static function readFileByLine($fileName, $key = "", $start = 0, $limit = 20, $revers = false)
    {
        if ($limit <= 0) {
            return array();
        }
        $start = $start < 0 ? 0 : $start;
        if (!$revers) {
            return self::readFileAsc($fileName, $key, $start, $limit);
        } else {
            return self::readFileDesc($fileName, $key, $start, $limit);
        }
    }

    /**
     * 正序读取文件
     *
     * @param string $fileName 文件名称
     * @param string $key      关键字
     * @param int    $start    起始行
     * @param int    $limit    数量
     *
     * @return array|string
     */
    private static function readFileAsc($fileName, $key = "", $start = 0, $limit = 20)
    {
        $result = array();
        if (!is_file($fileName) || !$fp = fopen($fileName, 'r')) {
            return "打开文件失败，请检查文件路径是否正确：" . $fileName;
        }
        $curLine = 0;
        while ($limit > 0 && !feof($fp) && $content = fgets($fp)) {
            $curLine++;
            if ($curLine >= $start && !empty(trim($content))) {
                if (empty($key)) {
                    array_push($result, $content);
                    $limit--;
                } elseif (mb_strpos($content, $key) !== false) {
                    array_push($result, $content);
                    $limit--;
                }
            }
        }
        fclose($fp);
        return $result;
    }

    /**
     * 倒叙读取文件
     *
     * @param string $fileName 文件名称
     * @param string $key      关键字
     * @param int    $start    起始行
     * @param int    $limit    数量
     *
     * @return array|string
     */
    private static function readFileDesc($fileName, $key = "", $start = 0, $limit = 20)
    {
        $result = array();
        if (!is_file($fileName) || !$fp = fopen($fileName, 'r')) {
            return "打开文件失败，请检查文件路径是否正确：" . $fileName;
        }
        $tempParams = ['cur_line' => 0, 'pos' => -2, 'eof' => ''];
        //输出文本中所有的行，直到文件结束为止。
        while ($limit > 0 && !feof($fp)) {
            while ($tempParams['eof'] != "\n") {//这里控制从文件的最后一行开始读
                if (!fseek($fp, $tempParams['pos'], SEEK_END)) {
                    $tempParams['eof'] = fgetc($fp);
                    $tempParams['pos']--;
                } else {
                    break;
                }
            }
            if (($content = fgets($fp)) == false) {
                fseek($fp, 0, SEEK_SET);
                if (($content = fgets($fp)) == false) {
                    break;
                }
                $limit = 0;
            }
            $tempParams['eof'] = "";
            $tempParams['cur_line']++;
            if (!empty(trim($content)) && $tempParams['cur_line'] >= $start) {
                if (empty($key)) {
                    array_push($result, $content);
                    $limit--;
                } elseif (mb_strpos($content, $key) !== false) {
                    array_push($result, $content);
                    $limit--;
                }
            }
        }
        fclose($fp);
        return $result;
    }
}
