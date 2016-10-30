<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Hisune\EchartsPHP\ECharts;
class Charts extends CI_Controller
{

    protected $echarts;
    protected $expire_date;
    protected $now;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Analysis_Mobile01_Model');
        $this->echarts = new ECharts();

        $this->expire_date = (int) $this->config->item('mobile01_expire_date') * 86400;
        $this->expire_date = strtotime(date('Y-m-d') . ' 00:00:00') - $this->expire_date;
        $this->expire_date = date('Y-m-d H:i:s', $this->expire_date);
        $this->now = date('Y-m-d') . ' 23:59:59';
    }

    public function brands_post_reply_num()
    {
        $params = $this->brands_post_reply_num_data();
        $this->draw_brands_post_reply_num_chart($params['forums'], $params['data']);
    }

    public function hot_brands()
    {
        $params = $this->hot_brands_data();
        $this->draw_hot_brands_chart($params['forums'], $params['data']);
    }

    public function hot_brands_by_date()
    {
        $params = $this->hot_brands_by_date_data();
        $this->draw_hot_brands_by_date_chart($params['forums'], $params['data'], $params['date_time_group']);
    }

    protected function brands_post_reply_num_data()
    {
        $forums = array('ACER', 'ASUS', 'SAMSUNG', 'SONY', 'XIAOMI');
        $data = array();
        $this->Analysis_Mobile01_Model->authur_date = array();
        $this->Analysis_Mobile01_Model->authur_date[] = $this->expire_date;
        $this->Analysis_Mobile01_Model->authur_date[] = $this->now;
        $this->Analysis_Mobile01_Model->get_post_reply_num();
        $data = $this->Analysis_Mobile01_Model->data_table;
        $params = array(
            'forums' => $forums,
            'data' => $data
        );

        return $params;
    }

    protected function draw_brands_post_reply_num_chart(array $forums = array(), array $data = array())
    {
        $this->echarts->backgroundColor = "new echarts.graphic.RadialGradient(0.3, 0.3, 0.8, [{offset: 0,color: '#f7f8fa'}, {offset: 1,color: '#cdd0d5'}])";
        $this->echarts->title = array('text' => '各品牌一週回文數');
        $this->echarts->legend = array(
            'right' => 10,
            'data' => $forums
        );
        $this->echarts->xAxis = array(
            'splitLine' => array(
                'lineStyle' => array('type' => 'dashed')
            )
        );
        $this->echarts->yAxis = array(
            'splitLine' => array(
                'lineStyle' => array('type' => 'dashed')
            ),
            'scale' => true
        );

        $series = array();

        $swap = array();
        $i = 0;
        foreach($data as $items) {
            $swap[$i] = array($items['post'], $items['reply'], $items['forums']);
            $i++;
        }

        $i = 0;
        foreach($swap as $items) {
            $series[$i]['name'] = $items[2];
            $series[$i]['data'] = array($items);
            $series[$i]['type'] = 'scatter';
            $series[$i]['symbolSize'] = 'function(data) {return Math.sqrt(data[1]) * 1.70;}';
            $series[$i]['label'] = array(
                'emphasis' => array(
                    'show' => true,
                    'formatter' => 'function(param) {return param.data[2];}',
                    'position' => 'top'
                )
            );
            $series[$i]['itemStyle'] = array(
                'normal' => array(
                    'shadowBlur' => 50,
                    'shadowOffsetY' => 5
                )
            );
            $i++;
        }

        $this->echarts->series = $series;
        echo $this->echarts->render('draw_brands_post_reply_num_chart');
    }

    protected function hot_brands_data()
    {
        $data = array();
        $forums = array('ACER', 'ASUS', 'SAMSUNG', 'SONY', 'XIAOMI');
        foreach($forums as $forum) {
            $this->Analysis_Mobile01_Model->forums = $forum;
            $this->Analysis_Mobile01_Model->authur_date = array();
            $this->Analysis_Mobile01_Model->authur_date[] = $this->expire_date;
            $this->Analysis_Mobile01_Model->authur_date[] = $this->now;
            $this->Analysis_Mobile01_Model->get_forum_post_num();
            $data[] = $this->Analysis_Mobile01_Model->data_table;
        }

        $params = array(
            'forums' => $forums,
            'data' => $data
        );

        return $params;
    }

    protected function hot_brands_by_date_data()
    {
        $date_time_group = array();

        for($i = (int) $this->config->item('mobile01_expire_date'); $i > 0; $i--) {
            $date_time_group[] = date('Y-m-d', strtotime(date('Y-m-d') . ' 00:00:00') - 86400 * $i);
        }

        $forums = array('ACER', 'ASUS', 'SAMSUNG', 'SONY', 'XIAOMI');
        $data = array();

        foreach($forums as $forum) {
            foreach($date_time_group as $date_time) {
                $this->Analysis_Mobile01_Model->forums = $forum;
                $this->Analysis_Mobile01_Model->authur_date = array();
                $this->Analysis_Mobile01_Model->authur_date[] = "{$date_time} 00:00:00";
                $this->Analysis_Mobile01_Model->authur_date[] = "{$date_time} 23:59:59";
                $this->Analysis_Mobile01_Model->get_forum_post_num();
                $data[$forum][] = $this->Analysis_Mobile01_Model->data_table;
            }
        }

        $params = array(
            'forums' => $forums,
            'data' => $data,
            'date_time_group' => $date_time_group
        );

        return $params;
    }

    protected function draw_hot_brands_by_date_chart(array $forums = array(), array $data = array(), array $date_time_group = array())
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->echarts->title = array('text' => '各品牌每天新發文數');
        $this->echarts->tooltip = array('trigger' => 'axis');
        $this->echarts->legend->data = $forums;
        $this->echarts->grid = array(
            'left' => '3%',
            'right' => '4%',
            'bottom' => '3%',
            'containLabel' => true
        );
        $this->echarts->toolbox = array(
            'feature' => array('saveAsImage' => array())
        );
        $this->echarts->xAxis[] = array(
            'type' => 'category',
            'boundaryGap' => false,
            'data' => $date_time_group
        );
        $this->echarts->yAxis[] = array(
            'type' => 'value'
        );

        $series = array();

        foreach($forums as $forum) {
            $series[] = array(
                'name' => $forum,
                'type' => 'line',
                'stack' => '總數',
                'data' => $data[$forum]
            );
        }

        $this->echarts->series = $series;
        echo $this->echarts->render('hot_brands_by_date');
    }

    protected function draw_hot_brands_chart(array $forums = array(), array $data = array())
    {
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
        $this->echarts->legend->data[] = '各品牌一週新發文數';
        $this->echarts->xAxis[] = array(
            'type' => 'category',
            'data' => $forums
        );
        $this->echarts->yAxis[] = array(
            'type' => 'value'
        );
        $this->echarts->series[] = array(
            'name' => '各品牌一週新發文數',
            'type' => 'bar',
            'data' => $data
        );
        echo $this->echarts->render('hot_brands');
    }
}
