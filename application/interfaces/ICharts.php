<?php
defined('BASEPATH') OR exit('No direct script access allowed');

interface ICharts
{
    /**
     * 取得各品牌每週發文數與回覆數資料
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料])
     */
    public function brands_post_reply_num_data() : array;

    /**
     * 繪制各品牌每週發文數與回覆數圖表
     * @param  array  $forums 各品牌
     * @param  array  $data   各品牌資料
     * @return void
     */
    public function draw_brands_post_reply_num_chart(array $forums = array(), array $data = array());

    /**
     * 取得各品牌每週發文數資料
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料])
     */
    public function hot_brands_data() : array;

    /**
     * 繪制各品牌每週發文數圖表
     * @param  array  $forums 各品牌
     * @param  array  $data   各品牌資料
     * @return void
     */
    public function draw_hot_brands_chart(array $forums = array(), array $data = array());

    /**
     * 取得各品牌每天發文數資料
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料], 'date_time_group' => [日期群組 YYYY-MM-DD HH:MM:SS])
     */
    public function hot_brands_by_date_data() : array;

    /**
     * 繪制各品牌每天發文數圖表
     * @param  array  $forums          各品牌
     * @param  array  $data            各品牌資料
     * @param  array  $date_time_group [日期群組 YYYY-MM-DD HH:MM:SS]
     * @return void
     */
    public function draw_hot_brands_by_date_chart(array $forums = array(), array $data = array(), array $date_time_group = array());
}
