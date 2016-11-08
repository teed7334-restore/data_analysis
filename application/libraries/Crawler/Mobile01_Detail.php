<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once(dirname(dirname(__FILE__)) . '/Base.php');
include_once(dirname(dirname((dirname(__FILE__)))) . '/interfaces/ICrawler.php');

/**
 * 爬取與處理Mobile01各討論串之資料
 */
class Mobile01_Detail extends Base implements ICrawler {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 將所有結構化資料寫入資料庫
     * @return void
     */
    public function saveAllAnalysisData()
    {
        $this->getWebsiteData();
    }

    /**
     * 將 Mobile01 討論區資料結構化
     * @param  string  $forums 品牌
     * @return void
     */
    public function analysisWebsiteData(string $forums = 'ACER')
    {

    }

    /**
     * 取得 Mobile01 網頁資料
     * @return void
     */
    public function getWebsiteData()
    {
        $this->CI->load->model('Analysis_Mobile01_Model');

        $this->CI->Analysis_Mobile01_Model->authur_date = array();
        $this->CI->Analysis_Mobile01_Model->authur_date[] = $this->expire_date;
        $this->CI->Analysis_Mobile01_Model->authur_date[] = $this->now;
        $this->CI->Analysis_Mobile01_Model->get_not_expire_date_data();
        $data = $this->CI->Analysis_Mobile01_Model->data_table;

        $this->CI->load->library('Convert/Mobile01_Detail_Convert', null, 'Mobile01_Detail_Convert');

        $this->CI->Mobile01_Detail_Convert->dropSchema();
        $this->CI->Mobile01_Detail_Convert->createSchema();

        foreach($data as $id => $analysis_mobile01) {

            $mobile01_forums_code = $analysis_mobile01['mobile01_forums_code'];
            $mobile01_thread_code = $analysis_mobile01['mobile01_thread_code'];

            $url = "http://www.mobile01.com/topicdetail.php?f={$mobile01_forums_code}&t={$mobile01_thread_code}";
            $forums = $this->getContent($url);

            if(200 !== $forums['status']) { //當無法取回網頁資料時
                continue;
            }

            $html = $forums['html'];

            $this->CI->Mobile01_Detail_Convert->id = $id;
            $this->CI->Mobile01_Detail_Convert->mobile01_forums_code = $mobile01_forums_code;
            $this->CI->Mobile01_Detail_Convert->mobile01_thread_code = $mobile01_thread_code;
            $this->CI->Mobile01_Detail_Convert->html = $html;
            $this->CI->Mobile01_Detail_Convert->dumpDatabase2Elasticsearch();

            sleep(1); //每抓一頁就休息一秒，以免抓太快被管理員封鎖
        }
    }
}
