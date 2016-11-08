<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 網頁爬蟲
 *
 * 流程︰
 * 1.取得網頁HTML
 * 2.將網頁HTML依品牌寫入快取
 * 3.從快取中將資料抓出做資料結構化
 * 4.將結構化之資料寫入資料庫
 */
class Crawler extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 將所有結構化資料寫入資料庫
     * @return void
     */
    public function run(string $service_name = '')
    {
        $this->load->library("Crawler/{$service_name}", null, $service_name);
        $this->Service = &$this->{$service_name};
        $this->Service->saveAllAnalysisData();
    }
}
