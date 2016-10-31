<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use JonnyW\PhantomJs\Client;
use Elasticsearch\ClientBuilder;

/**
 * 網頁爬蟲
 *
 * 流程︰
 * 1.取得Mobile01網頁HTML
 * 2.將Mobile01網頁HTML依品牌寫入快取
 * 3.從快取中將資料抓出做資料結構化
 * 4.將結構化之資料寫入資料庫
 */
class Crawler extends CI_Controller
{

    protected $phantomjs; //phatnomjs 物件
    protected $elasticsearch; //elasticsearch 物件

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->driver('cache');
        include('./vendor/autoload.php');
        $this->phantomjs = Client::getInstance();
        $this->phantomjs->getEngine()->setPath(dirname(dirname(dirname(__FILE__))) . '/bin/phantomjs');
        $this->phantomjs->isLazy();
        $this->elasticsearch = ClientBuilder::create()->build();
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set("memory_limit","2048M");
    }

    /**
     * 將所有結構化資料寫入資料庫
     * @return void
     */
    public function saveAllAnalysisMobile01Data()
    {
        $this->getMobile01();

        $brands = array('ACER', 'ASUS', 'SAMSUNG', 'SONY', 'XIAOMI');
        foreach($brands as $brand)
            $this->analysisMobile01($brand);
    }

    /**
     * 結Mobile01討論區資料結構化
     * @param  string  $forums 品牌
     * @return void
     */
    protected function analysisMobile01(string $forums = 'ACER')
    {
        $expire_date = (int) $this->config->item('mobile01_expire_date') * 86400;
        $expire_date = strtotime(date('Y-m-d') . ' 00:00:00') - $expire_date;

        $page = $this->cache->redis->get($forums);

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

        $this->load->model('Analysis_Mobile01_Model');

        $html = $html->find('table[summary=文章列表] tbody tr');

        foreach($html as $td) {

            if(empty($td->find('td.subject', 0)))
                continue;

            if($expire_date > strtotime($td->find('td.authur p', 0)->plaintext))
                continue;

            $url = $td->find('td.subject a', 0)->href;
            $params = parse_url($url, PHP_URL_QUERY);
            parse_str($params, $params);
            $this->Analysis_Mobile01_Model->forums = $forums;
            $this->Analysis_Mobile01_Model->mobile01_forums_code = $params['f'];
            $this->Analysis_Mobile01_Model->mobile01_thread_code = $params['amp;t'];

            //檢查資料庫中是否有重覆資料
            $this->Analysis_Mobile01_Model->get_have_data();
            $data = $this->Analysis_Mobile01_Model->data_table;

            if(0 < count($data)) { //當如果資料庫車有重覆資料時，只修正熱門度、回覆數、最後回覆時間
                $this->Analysis_Mobile01_Model->subject = $td->find('td.subject', 0)->plaintext;
                $title = explode('人氣:', $td->find('td.subject a', 0)->title);
                $this->Analysis_Mobile01_Model->hot = (int) $title[1];
                $this->Analysis_Mobile01_Model->reply = $td->find('td.reply', 0)->plaintext;
                $this->Analysis_Mobile01_Model->latest_replay_date = $td->find('td.latestreply p', 0)->plaintext;
                $this->Analysis_Mobile01_Model->edit_have_data();
            }
            else { //當資料庫中沒有資料時，寫入所有資料
                $this->Analysis_Mobile01_Model->forums = $forums;
                $this->Analysis_Mobile01_Model->subject = $td->find('td.subject', 0)->plaintext;
                $title = explode('人氣:', $td->find('td.subject a', 0)->title);
                $this->Analysis_Mobile01_Model->hot = (int) $title[1];
                $this->Analysis_Mobile01_Model->reply = $td->find('td.reply', 0)->plaintext;
                $this->Analysis_Mobile01_Model->authur = $td->find('td.authur p', 1)->plaintext;
                $this->Analysis_Mobile01_Model->authur_date = $td->find('td.authur p', 0)->plaintext;
                $this->Analysis_Mobile01_Model->latest_replay_date = $td->find('td.latestreply p', 0)->plaintext;
                $this->Analysis_Mobile01_Model->mobile01_forums_code = $params['f'];
                $this->Analysis_Mobile01_Model->mobile01_thread_code = $params['amp;t'];
                $this->Analysis_Mobile01_Model->add();
            }
        }
    }

    /**
     * 取得Mobile01網頁資料
     * @return void
     */
    protected function getMobile01()
    {
        $url = array( //各品牌與其對應的網址
            'ACER' => 'http://www.mobile01.com/topiclist.php?f=564',
            'ASUS' => 'http://www.mobile01.com/topiclist.php?f=588',
            'SAMSUNG' => 'http://www.mobile01.com/topiclist.php?f=568',
            'SONY' => 'http://www.mobile01.com/topiclist.php?f=569',
            'XIAOMI' => 'http://www.mobile01.com/topiclist.php?f=634'
        );

        $this->load->model('Analysis_Mobile01_Log_Model');

        foreach($url as $forums => $url) {

            $data = $this->getContent($url);
            $this->cache->redis->save($forums, $data['html'], 86400); //先將取回的資料放到快取做暫存

            if(200 !== $data['status']) { //當無法取回網頁資料時，將錯誤訊息寫入到資料庫
                $this->Analysis_Mobile01_Log_Model->forums = $forums;
                $this->Analysis_Mobile01_Log_Model->status_code = $data['status'];
                $this->Analysis_Mobile01_Log_Model->run_at = date('Y-m-d H:i:s');
                $this->Analysis_Mobile01_Log_Model->add();
            }
            sleep(1); //每抓一頁就休息一秒，以勉抓太快被管理員封鎖
        }
    }

    /**
     * 取得網頁資料共用元件
     * @param  string $url    網址
     * @param  string $method POST/GET
     * @return array          array('status' => [HTTP訊息代碼], 'html' => [網頁HTML])
     */
    protected function getContent(string $url = '', string $method = 'GET') : array
    {
        $data = array();
        $timeout = 10000; //網頁抓取Timeout時間

        $request = $this->phantomjs->getMessageFactory()->createRequest($url, $method);
        $request->setTimeout($timeout);
        $response = $this->phantomjs->getMessageFactory()->createResponse();
        $this->phantomjs->send($request, $response);

        $status = $response->getStatus(); //取得HTTP訊息代碼
        if(200 === $status) { //可以正常取得網頁HTML時
            $data['status'] = $status;
            $data['html'] = $response->getContent(); //取得網頁HTML
            return $data;
        }

        //無法正常取得網頁HTML時
        $data['status'] = $status;
        $data['html'] = '';

        return $data;
    }
}
