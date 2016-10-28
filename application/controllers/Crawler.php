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
        $this->elasticsearch = ClientBuilder::create()->build();
    }

    public function getMobile01()
    {
        set_time_limit(0);
        ini_set("memory_limit","2048M");

        $url = array(
            'ACER' => 'http://www.mobile01.com/topiclist.php?f=564',
            'ASUS' => 'http://www.mobile01.com/topiclist.php?f=588',
            'SAMSUNG' => 'http://www.mobile01.com/topiclist.php?f=568',
            'SONY' => 'http://www.mobile01.com/topiclist.php?f=569',
            'XIAOMI' => 'http://www.mobile01.com/topiclist.php?f=634'
        );

        foreach($url as $company => $url) {
            $html = $this->getContent($url);
            $this->cache->redis->save($company, $html, 86400);
            sleep(1);
        }
    }

    public function saveAllAnalysisMobile01Data()
    {
        $brands = array('ACER', 'ASUS', 'SAMSUNG', 'SONY', 'XIAOMI');
        foreach($brands as $brand)
            $this->analysisMobile01($brand);
    }

    protected function getContent(string $url = '', string $method = 'GET') : string
    {
        $request = $this->phantomjs->getMessageFactory()->createRequest($url, $method);
        $response = $this->phantomjs->getMessageFactory()->createResponse();
        $this->phantomjs->send($request, $response);

        if($response->getStatus() === 200)
            return $response->getContent();

        return '';
    }

    protected function analysisMobile01(string $forums = 'ACER')
    {
        $expire_date = (int) $this->config->item('mobile01_expire_date') * 86400;
        $expire_date = strtotime(date('Y-m-d') . ' 00:00:00') - $expire_date;

        $page = $this->cache->redis->get($forums);
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
