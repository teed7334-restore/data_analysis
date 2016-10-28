<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Hisune\EchartsPHP\ECharts;
class Home extends CI_Controller
{

    protected $echarts;

    public function __construct()
    {
        parent::__construct();
        $this->echarts = new ECharts();
    }

    public function hot_brands()
    {
        $expire_date = (int) $this->config->item('mobile01_expire_date') * 86400;
        $expire_date = strtotime(date('Y-m-d') . ' 00:00:00') - $expire_date;
        $expire_date = date('Y-m-d H:i:s', $expire_date);
        $now = date('Y-m-d') . ' 23:59:59';

        $data = array();
        $this->load->model('Analysis_Mobile01_Model');
        $brands = array('ACER', 'ASUS', 'SAMSUNG', 'SONY', 'XIAOMI');
        foreach($brands as $brand) {
            $this->Analysis_Mobile01_Model->forums = $brand;
            $this->Analysis_Mobile01_Model->authur_date = array();
            $this->Analysis_Mobile01_Model->authur_date[] = $expire_date;
            $this->Analysis_Mobile01_Model->authur_date[] = $now;
            $this->Analysis_Mobile01_Model->get_forum_post_num();
            $data[] = $this->Analysis_Mobile01_Model->data_table;
        }

        $this->load->helper('html');
        $this->load->helper('url');

        header('Content-Type: text/html; charset=utf-8');
        $this->echarts->tooltip = array(
            'show' => true,
            'trigger' => 'axis',
            'axisPointer' => array(
                'type' => 'shadow'
            )
        );
        $this->echarts->grid = array(
            'left' => '3%',
            'right' => '4%',
            'bottom' => '3%',
            'containLabel' => true
        );
        $this->echarts->legend->data[] = '新文章數';
        $this->echarts->xAxis[] = array(
            'type' => 'category',
            'data' => $brands
        );
        $this->echarts->yAxis[] = array(
            'type' => 'value'
        );
        $this->echarts->series[] = array(
            'name' => '新文章數',
            'type' => 'bar',
            'data' => $data
        );
        echo $this->echarts->render('simple-custom-id');
    }

	public function index()
	{
		$this->load->view('welcome_message');
	}
}
