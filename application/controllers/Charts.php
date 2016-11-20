<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * 顯示圖表
 *
 * 流程︰
 * 1.檢查快取是否有所需資料，若無，則從第2步開始；若有，則從第4步開始
 * 2.從資料庫取得資料
 * 3.將資料庫的資料寫入快取
 * 4.將資料送交繪圖函式
 * 5.顯示圖表
 */
class Charts extends CI_Controller
{
    protected $Service;

    public function __construct()
    {
        parent::__construct();
        $service_name = 'Mobile01';
        $this->load->library("Charts/{$service_name}", null, $service_name);
        $this->Service = &$this->{$service_name};
    }
    
    /**
     * 顯示各品牌每週發文數與回覆數圖表
     * @return void
     */
    public function brands_post_reply_num()
    {
        $params = $this->Service->brands_post_reply_num_data();
        $this->Service->draw_brands_post_reply_num_chart($params['forums'], $params['data']);
    }

    /**
     * 顯示各品牌每週發文數圖表
     * @return void
     */
    public function hot_brands()
    {
        $params = $this->Service->hot_brands_data();
        $this->Service->draw_hot_brands_chart($params['forums'], $params['data']);
    }

    /**
     * 顯示各品牌每日發文數圖表
     * @return void
     */
    public function hot_brands_by_date()
    {
        $params = $this->Service->hot_brands_by_date_data();
        $this->Service->draw_hot_brands_by_date_chart($params['forums'], $params['data'], $params['date_time_group']);
    }
}
