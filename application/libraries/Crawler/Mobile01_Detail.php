<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once(dirname(dirname(__FILE__)) . '/Base.php');
include_once(dirname(dirname((dirname(__FILE__)))) . '/interfaces/ICrawler.php');

/**
 * 爬取與處理Mobile01各討論串之資料
 */
class Mobile01_Detail extends Base implements ICrawler {

    public $params;
    public $forums;

    public function __construct()
    {
        parent::__construct();
        $this->params = array();
        $this->forums = array();
    }

    /**
     * 將所有結構化資料寫入資料庫
     * @return void
     */
    public function saveAllAnalysisData()
    {
        $forums = array('ACER', 'ASUS', 'SAMSUNG', 'SONY', 'XIAOMI');
        $this->getWebsiteData();
        foreach ($forums as $brand)
            $this->analysisWebsiteData($brand);
        print_r($this->forums);
    }

    /**
     * 將 Mobile01 討論區資料結構化
     * @param  string  $forums 品牌
     * @return void
     */
    public function analysisWebsiteData(string $forums = 'ACER')
    {
        $data = $this->jieba_cut($this->params[$forums]);
        foreach($data as $words) {
            $words = trim($words);
            if(2 > mb_strlen($words)) //移除少於二字元的字串
                continue;
            if(!isset($this->forums[$forums][$words]))
                $this->forums[$forums][$words] = 1;
            else
                $this->forums[$forums][$words]++;
        }

        arsort($this->forums[$forums]); //依陣列的值由大到小排序

        $swap = array();
        $i = 0;

        //只取前十筆資料
        foreach($this->forums[$forums] as $word => $num) {
            if(10 <= $i)
                break;
            $swap[$word] = $num;
            $i++;
        }

        $this->forums[$forums] = $swap;
        unset($swap);
    }

    /**
     * 取得 Mobile01 網頁資料
     * @return void
     */
    public function getWebsiteData()
    {
        $this->CI->load->model('Mock_Model');
        $acer_txt = $this->CI->Mock_Model->get_acer_txt();
        $asus_txt = $this->CI->Mock_Model->get_asus_txt();
        $samsung_txt = $this->CI->Mock_Model->get_samsung_txt();
        $sony_txt = $this->CI->Mock_Model->get_sony_txt();
        $xiaomi_txt = $this->CI->Mock_Model->get_xiaomi_txt();
        $this->params = array(
            'ACER' => $acer_txt,
            'ASUS' => $asus_txt,
            'SAMSUNG' => $samsung_txt,
            'SONY' => $sony_txt,
            'XIAOMI' => $xiaomi_txt
        );
    }
}
