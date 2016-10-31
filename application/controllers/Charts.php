<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Hisune\EchartsPHP\ECharts;

/**
 * 顯示圖表
 *
 * 流程︰
 * 1.檢查快取是否有所需資料，若無，則從第2步開始；若有，則從第4步開始
 * 2.從資料庫取得資料
 * 3.將資料庫的資料寫入快取
 * 4.將資料送交繪圖函式
 * 5.顯示圖表
 */
class Charts extends CI_Controller
{

    protected $echarts; //echats 物件
    protected $expire_date; //起始日
    protected $now; //結束日

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Analysis_Mobile01_Model');
        $this->echarts = new ECharts();

        $this->load->driver('cache');

        $this->expire_date = (int) $this->config->item('mobile01_expire_date') * 86400;
        $this->expire_date = strtotime(date('Y-m-d') . ' 00:00:00') - $this->expire_date;
        $this->expire_date = date('Y-m-d H:i:s', $this->expire_date);
        $this->now = date('Y-m-d') . ' 23:59:59';
    }

    /**
     * 顯示各品牌每週發文數與回覆數圖表
     * @return void
     */
    public function brands_post_reply_num()
    {
        $params = $this->brands_post_reply_num_data();
        $this->draw_brands_post_reply_num_chart($params['forums'], $params['data']);
    }

    /**
     * 顯示各品牌每週發文數圖表
     * @return void
     */
    public function hot_brands()
    {
        $params = $this->hot_brands_data();
        $this->draw_hot_brands_chart($params['forums'], $params['data']);
    }

    /**
     * 顯示各品牌每日發文數圖表
     * @return void
     */
    public function hot_brands_by_date()
    {
        $params = $this->hot_brands_by_date_data();
        $this->draw_hot_brands_by_date_chart($params['forums'], $params['data'], $params['date_time_group']);
    }

    /**
     * 取得各品牌每週發文數與回覆數資料
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料])
     */
    protected function brands_post_reply_num_data() : array
    {
        $params = $this->brands_post_reply_num_data_from_cache();

        if(empty($params))
            $params = $this->brands_post_reply_num_data_from_database();

        return $params;
    }

    /**
     * 從資料庫取得各品牌每週發文數與回覆數
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料])
     */
    protected function brands_post_reply_num_data_from_database() : array
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

        $key = "brands_post_reply_num_data";
        $this->cache->redis->save($key, $params, 300);

        return $params;
    }

    /**
     * 從快取取得各品牌每週發文數與回覆數
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料])
     */
    protected function brands_post_reply_num_data_from_cache() : array
    {
        $key = "brands_post_reply_num_data";
        $params = $this->cache->redis->get($key);

        if(!$params)
            return array();

        return $params;
    }

    /**
     * 繪制各品牌每週發文數與回覆數圖表
     * @param  array  $forums 各品牌
     * @param  array  $data   各品牌資料
     * @return void
     */
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

    /**
     * 取得各品牌每週發文數資料
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料])
     */
    protected function hot_brands_data() : array
    {
        $params = $this->hot_brands_data_from_cache();

        if(empty($params))
            $params = $this->hot_brands_data_from_database();

        return $params;
    }

    /**
     * 從資料庫取得各品牌每週發文數
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料])
     */
    protected function hot_brands_data_from_database() : array
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

        $key = "hot_brands_data";
        $this->cache->redis->save($key, $params, 300);

        return $params;
    }

    /**
     * 從快取取得各品牌每週發文數
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料])
     */
    protected function hot_brands_data_from_cache() : array
    {
        $key = "hot_brands_data";
        $params = $this->cache->redis->get($key);

        if(!$params)
            return array();

        return $params;
    }

    /**
     * 繪制各品牌每週發文數圖表
     * @param  array  $forums 各品牌
     * @param  array  $data   各品牌資料
     * @return void
     */
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

    /**
     * 取得各品牌每天發文數資料
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料], 'date_time_group' => [日期群組 YYYY-MM-DD HH:MM:SS])
     */
    protected function hot_brands_by_date_data() : array
    {
        $params = $this->hot_brands_by_date_data_from_cache();

        if(empty($params))
            $params = $this->hot_brands_by_date_data_from_database();

        return $params;
    }

    /**
     * 從資料庫取得各品牌每天發文數
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料], 'date_time_group' => [日期群組 YYYY-MM-DD HH:MM:SS])
     */
    protected function hot_brands_by_date_data_from_database() : array
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

        $key = "hot_brands_by_date_data";
        $this->cache->redis->save($key, $params, 300);

        return $params;
    }

    /**
     * 從快取取得各品牌每天發文數
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料], 'date_time_group' => [日期群組 YYYY-MM-DD HH:MM:SS])
     */
    protected function hot_brands_by_date_data_from_cache() : array
    {
        $key = "hot_brands_by_date_data";
        $params = $this->cache->redis->get($key);

        if(!$params)
            return array();

        return $params;
    }

    /**
     * 繪制各品牌每天發文數圖表
     * @param  array  $forums          各品牌
     * @param  array  $data            各品牌資料
     * @param  array  $date_time_group [日期群組 YYYY-MM-DD HH:MM:SS]
     * @return void
     */
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
}
