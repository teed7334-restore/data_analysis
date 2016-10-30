<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use JonnyW\PhantomJs\Client;
use Elasticsearch\ClientBuilder;

class Crawler extends CI_Controller
{

    protected $phantomjs;
    protected $elasticsearch;

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

    public function saveAllAnalysisMobile01Data()
    {
        $this->getMobile01();

        $brands = array('ACER', 'ASUS', 'SAMSUNG', 'SONY', 'XIAOMI');
        foreach($brands as $brand)
            $this->analysisMobile01($brand);
    }

    public function removeElasticSearchIndex()
    {
        $params = array('index' => 'data_analysis');
        $this->elasticsearch->indices()->delete($params);
    }

    protected function getMobile01()
    {
        $url = array(
            'ACER' => 'http://www.mobile01.com/topiclist.php?f=564',
            'ASUS' => 'http://www.mobile01.com/topiclist.php?f=588',
            'SAMSUNG' => 'http://www.mobile01.com/topiclist.php?f=568',
            'SONY' => 'http://www.mobile01.com/topiclist.php?f=569',
            'XIAOMI' => 'http://www.mobile01.com/topiclist.php?f=634'
        );

        $this->load->model('Analysis_Mobile01_Log_Model');

        foreach($url as $forums => $url) {

            $data = $this->getContent($url);
            $this->cache->redis->save($forums, $data['html'], 86400);

            if(200 !== $data['status']) {
                $this->Analysis_Mobile01_Log_Model->forums = $forums;
                $this->Analysis_Mobile01_Log_Model->status_code = $data['status'];
                $this->Analysis_Mobile01_Log_Model->run_at = date('Y-m-d H:i:s');
                $this->Analysis_Mobile01_Log_Model->add();
            }
            sleep(1);
        }
    }

    protected function getContent(string $url = '', string $method = 'GET') : array
    {
        $data = array();
        $timeout = 10000;

        $request = $this->phantomjs->getMessageFactory()->createRequest($url, $method);
        $request->setTimeout($timeout);
        $response = $this->phantomjs->getMessageFactory()->createResponse();
        $this->phantomjs->send($request, $response);

        $status = $response->getStatus();
        if(200 === $status) {
            $data['status'] = $status;
            $data['html'] = $response->getContent();
            return $data;
        }

        $data['status'] = $status;
        $data['html'] = '';

        return $data;
    }

    protected function analysisMobile01(string $forums = 'ACER')
    {
        $expire_date = (int) $this->config->item('mobile01_expire_date') * 86400;
        $expire_date = strtotime(date('Y-m-d') . ' 00:00:00') - $expire_date;

        $page = $this->cache->redis->get($forums);

        if('' === $page)
            return;

        $html = str_get_html($page);

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

            $this->Analysis_Mobile01_Model->get_have_data();
            $data = $this->Analysis_Mobile01_Model->data_table;

            if(0 < count($data)) {
                $this->Analysis_Mobile01_Model->subject = $td->find('td.subject', 0)->plaintext;
                $title = explode('人氣:', $td->find('td.subject a', 0)->title);
                $this->Analysis_Mobile01_Model->hot = (int) $title[1];
                $this->Analysis_Mobile01_Model->reply = $td->find('td.reply', 0)->plaintext;
                $this->Analysis_Mobile01_Model->authur = $td->find('td.authur p', 1)->plaintext;
                $this->Analysis_Mobile01_Model->authur_date = $td->find('td.authur p', 0)->plaintext;
                $this->Analysis_Mobile01_Model->latest_replay_date = $td->find('td.latestreply p', 0)->plaintext;
                $this->Analysis_Mobile01_Model->edit_have_data();
            }
            else {
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
}
