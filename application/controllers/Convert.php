<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 資料庫資料轉去搜尋引擎
 *
 */
class Convert extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $service_name = 'Mobile01';
        $this->load->library("Convert/{$service_name}", null, $service_name);
        $this->Service = &$this->{$service_name};
    }

    /**
     * 將所有資料庫資料寫去搜尋引擎
     * @return void
     */
    public function run()
    {
        $this->Service->dumpDatabase2Elasticsearch();
    }
}
