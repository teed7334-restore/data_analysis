<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Elasticsearch\ClientBuilder;

class Base {

    public $CI; //CI資源
    public $Elasticsearch; //Elasticsearch 物件

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('Analysis_Mobile01_Model');
        $this->Elasticsearch = ClientBuilder::create()->build();
        set_time_limit(0); //PHP Timeout 時間無限制
        ini_set('max_execution_time', 0); //PHP 最大執行時間無限制
        ini_set("memory_limit","2048M"); //最高可用到2GB RAM
    }
}
