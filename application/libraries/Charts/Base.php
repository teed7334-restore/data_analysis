<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Hisune\EchartsPHP\ECharts;

class Base {

    public $CI; //CI資源
    public $ECharts; //ECharts 物件
    public $expire_date; //起始日
    public $now; //結束日

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('Analysis_Mobile01_Model');
        $this->ECharts = new ECharts();

        $this->CI->load->driver('cache');

        $this->expire_date = (int) $this->CI->config->item('mobile01_expire_date') * 86400;
        $this->expire_date = strtotime(date('Y-m-d') . ' 00:00:00') - $this->expire_date;
        $this->expire_date = date('Y-m-d H:i:s', $this->expire_date);
        $this->now = date('Y-m-d') . ' 23:59:59';
    }
}
