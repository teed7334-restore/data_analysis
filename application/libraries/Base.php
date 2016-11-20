<?php
defined('BASEPATH') OR exit('No direct script access allowed');

set_time_limit(0); //PHP Timeout 時間無限制
ini_set('max_execution_time', 0); //PHP 最大執行時間無限制
ini_set("memory_limit","2048M"); //最高可用到2048MB RAM

use Hisune\EchartsPHP\ECharts;
use Elasticsearch\ClientBuilder;
use JonnyW\PhantomJs\Client;
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
Jieba::init();
Finalseg::init();

class Base {

    public $CI; //CI資源
    public $ECharts; //ECharts 物件
    public $Elasticsearch; //Elasticsearch 物件
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

        $this->Elasticsearch = ClientBuilder::create()->build();

        $this->PhatnomJS = Client::getInstance();
        $this->PhatnomJS->getEngine()->setPath(dirname(dirname(dirname(__FILE__))) . '/bin/phantomjs'); //PhantomJS 主程式路徑
        $this->PhatnomJS->isLazy(); //等待 HTML DOM 完全 Load 完成，且 AJAX 通通跑完
    }

    public function jieba_cut(string $txt = '')
    {
        return Jieba::cut($txt);
    }

    /**
     * 取得網頁資料共用元件
     * @param  string $url    網址
     * @param  string $method POST/GET
     * @return array          array('status' => [HTTP訊息代碼], 'html' => [網頁HTML])
     */
    public function getContent(string $url = '', string $method = 'GET') : array
    {
        $data = array();
        $timeout = 10000; //網頁抓取Timeout時間
        $request = $this->PhatnomJS->getMessageFactory()->createRequest($url, $method);
        $request->setTimeout($timeout);
        $response = $this->PhatnomJS->getMessageFactory()->createResponse();
        $this->PhatnomJS->send($request, $response);
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
