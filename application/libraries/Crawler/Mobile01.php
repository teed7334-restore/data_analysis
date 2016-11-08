<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include(dirname(dirname(__FILE__)) . 'Base.php');
include(dirname(dirname((dirname(__FILE__)))) . '/interfaces/ICrawler.php');

/**
 * 爬取與處理Mobile01之資料
 */
class Mobile01 extends Base implements ICrawler {

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

        $brands = array('ACER', 'ASUS', 'SAMSUNG', 'SONY', 'XIAOMI');
        foreach($brands as $brand)
            $this->analysisWebsiteData($brand);
    }

    /**
     * 將 Mobile01 討論區資料結構化
     * @param  string  $forums 品牌
     * @return void
     */
    public function analysisWebsiteData(string $forums = 'ACER')
    {
        $expire_date = (int) $this->CI->config->item('mobile01_expire_date') * 86400;
        $expire_date = strtotime(date('Y-m-d') . ' 00:00:00') - $expire_date;

        $page = $this->CI->cache->redis->get($forums);

        if('' === $page) //當網頁無法取回資料時，直接退出
            return;

        $html = str_get_html($page);

        //建立要寫入資料庫的Schema
        $subject = array();
        $hot = array();
        $reply = array();
        $authur = array();
        $authur_date = array();
        $latest_replay_date = array();
        $mobile01_forums_code = array();
        $mobile01_thread_code = array();

        $this->CI->load->model('Analysis_Mobile01_Model');

        $html = $html->find('table[summary=文章列表] tbody tr');

        foreach($html as $td) {

            if(empty($td->find('td.subject', 0)))
                continue;

            if($expire_date > strtotime($td->find('td.authur p', 0)->plaintext))
                continue;

            $url = $td->find('td.subject a', 0)->href;
            $params = parse_url($url, PHP_URL_QUERY);
            parse_str($params, $params);
            $this->CI->Analysis_Mobile01_Model->forums = $forums;
            $this->CI->Analysis_Mobile01_Model->mobile01_forums_code = $params['f'];
            $this->CI->Analysis_Mobile01_Model->mobile01_thread_code = $params['amp;t'];

            //檢查資料庫中是否有重覆資料
            $this->CI->Analysis_Mobile01_Model->get_have_data();
            $data = $this->CI->Analysis_Mobile01_Model->data_table;

            if(0 < count($data)) { //當如果資料庫車有重覆資料時，只修正熱門度、回覆數、最後回覆時間
                $this->CI->Analysis_Mobile01_Model->subject = $td->find('td.subject', 0)->plaintext;
                $title = explode('人氣:', $td->find('td.subject a', 0)->title);
                $this->CI->Analysis_Mobile01_Model->hot = (int) $title[1];
                $this->CI->Analysis_Mobile01_Model->reply = $td->find('td.reply', 0)->plaintext;
                $this->CI->Analysis_Mobile01_Model->latest_replay_date = $td->find('td.latestreply p', 0)->plaintext;
                $this->CI->Analysis_Mobile01_Model->edit_have_data();
            }
            else { //當資料庫中沒有資料時，寫入所有資料
                $this->CI->Analysis_Mobile01_Model->forums = $forums;
                $this->CI->Analysis_Mobile01_Model->subject = $td->find('td.subject', 0)->plaintext;
                $title = explode('人氣:', $td->find('td.subject a', 0)->title);
                $this->CI->Analysis_Mobile01_Model->hot = (int) $title[1];
                $this->CI->Analysis_Mobile01_Model->reply = $td->find('td.reply', 0)->plaintext;
                $this->CI->Analysis_Mobile01_Model->authur = $td->find('td.authur p', 1)->plaintext;
                $this->CI->Analysis_Mobile01_Model->authur_date = $td->find('td.authur p', 0)->plaintext;
                $this->CI->Analysis_Mobile01_Model->latest_replay_date = $td->find('td.latestreply p', 0)->plaintext;
                $this->CI->Analysis_Mobile01_Model->mobile01_forums_code = $params['f'];
                $this->CI->Analysis_Mobile01_Model->mobile01_thread_code = $params['amp;t'];
                $this->CI->Analysis_Mobile01_Model->add();
            }
        }
    }

    /**
     * 取得 Mobile01 網頁資料
     * @return void
     */
    public function getWebsiteData()
    {
        $url = array( //各品牌與其對應的網址
            'ACER' => 'http://www.mobile01.com/topiclist.php?f=564',
            'ASUS' => 'http://www.mobile01.com/topiclist.php?f=588',
            'SAMSUNG' => 'http://www.mobile01.com/topiclist.php?f=568',
            'SONY' => 'http://www.mobile01.com/topiclist.php?f=569',
            'XIAOMI' => 'http://www.mobile01.com/topiclist.php?f=634'
        );

        $this->CI->load->model('Analysis_Mobile01_Log_Model');

        foreach($url as $forums => $url) {

            $data = $this->getContent($url);
            $this->CI->cache->redis->save($forums, $data['html'], 86400); //先將取回的資料放到快取做暫存

            if(200 !== $data['status']) { //當無法取回網頁資料時，將錯誤訊息寫入到資料庫
                $this->CI->Analysis_Mobile01_Log_Model->forums = $forums;
                $this->CI->Analysis_Mobile01_Log_Model->status_code = $data['status'];
                $this->CI->Analysis_Mobile01_Log_Model->run_at = date('Y-m-d H:i:s');
                $this->CI->Analysis_Mobile01_Log_Model->add();
            }
            sleep(1); //每抓一頁就休息一秒，以免抓太快被管理員封鎖
        }
    }
}
