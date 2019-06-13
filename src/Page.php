<?php
namespace php_ext;

/**
 * 分页辅助类
 * Class Page
 *
 * @package php_ext
 */
class Page
{
    /**
     * 格式化分页返回数据
     *
     * @param think\Paginator|mixed $paginator 分页对象数据
     *
     * @return array
     */
    public static function format($paginator)
    {
        $returnData = [
            'is_empty' => $paginator->isEmpty(),   // 当前返回数据是否空
            'total' => $paginator->total(),   // 总数据条数
            'count' => $paginator->count(),   // 当前页面的条数
            'list_rows' => $paginator->listRows(), // 每页的数据条数
            'current_page' => $paginator->currentPage(),   // 当前页页码
            'total_page' => ceil($paginator->total() / $paginator->listRows()),   // 总页数
            'has_more' => $paginator->hasPages(),  // 是否还有下一页
            'last_page' => $paginator->lastPage(), // 最后一页的页码
            'items' => $paginator->items(), // 数据
        ];
        return $returnData;
    }

    /**
     * 获取分页数据
     *
     * @param int   $total 总数量
     * @param int   $rows  一页行数
     * @param int   $page  第几页
     * @param array $items 数据
     *
     * @return array
     */
    public static function formatData($total, $rows = 10, $page = 1, $items = [])
    {
        $total = $total < 0 ? 0 : $total;
        $totalPage = ceil($total * 1.0 / $rows);
        $count = 0;
        if ($page <= $totalPage) {
            $rNum = $total - $rows * ($page - 1);
            $count = $rows > $rNum ? $rNum : $rows;
        }
        return [
            'is_empty' => !$count,   // 当前返回数据是否空
            'total' => $total,   // 总数据条数
            'count' => $count,   // 当前页面的条数
            'list_rows' => $rows, // 每页的数据条数
            'current_page' => $page,   // 当前页页码
            'total_page' => $totalPage,   // 总页数
            'has_more' => $totalPage > $page,  // 是否还有下一页
            'last_page' => $totalPage, // 最后一页的页码
            'items' => $items, // 数据
        ];
    }
}