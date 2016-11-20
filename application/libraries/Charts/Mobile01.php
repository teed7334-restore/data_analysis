<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once(dirname(dirname(__FILE__)) . '/Base.php');
include_once(dirname(dirname((dirname(__FILE__)))) . '/interfaces/ICharts.php');

/**
 * 處理與繪制Mobile01分析後之資料與圖表
 */
class Mobile01 extends Base implements ICharts {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取得各品牌每週發文數與回覆數資料
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料])
     */
    public function brands_post_reply_num_data() : array
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
    public function brands_post_reply_num_data_from_database() : array
    {
        $forums = array('ACER', 'ASUS', 'SAMSUNG', 'SONY', 'XIAOMI');
        $data = array();
        $this->CI->Analysis_Mobile01_Model->authur_date = array();
        $this->CI->Analysis_Mobile01_Model->authur_date[] = $this->expire_date;
        $this->CI->Analysis_Mobile01_Model->authur_date[] = $this->now;
        $this->CI->Analysis_Mobile01_Model->get_post_reply_num();
        $data = $this->CI->Analysis_Mobile01_Model->data_table;
        $params = array(
            'forums' => $forums,
            'data' => $data
        );

        $key = "brands_post_reply_num_data";
        $this->CI->cache->redis->save($key, $params, 300);

        return $params;
    }

    /**
     * 從快取取得各品牌每週發文數與回覆數
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料])
     */
    public function brands_post_reply_num_data_from_cache() : array
    {
        $key = "brands_post_reply_num_data";
        $params = $this->CI->cache->redis->get($key);

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
    public function draw_brands_post_reply_num_chart(array $forums = array(), array $data = array())
    {
        $this->ECharts->backgroundColor = "new ECharts.graphic.RadialGradient(0.3, 0.3, 0.8, [{offset: 0,color: '#f7f8fa'}, {offset: 1,color: '#cdd0d5'}])";
        $this->ECharts->title = array('text' => '各品牌一週發文/回文統計');
        $this->ECharts->legend = array(
            'right' => 10,
            'data' => $forums
        );
        $this->ECharts->xAxis = array(
            'name' => '發文數',
            'splitLine' => array(
                'lineStyle' => array('type' => 'dashed')
            )
        );
        $this->ECharts->yAxis = array(
            'name' => '回覆數',
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

        $this->ECharts->series = $series;
        echo $this->ECharts->render('draw_brands_post_reply_num_chart');
    }

    /**
     * 取得各品牌每週發文數資料
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料])
     */
    public function hot_brands_data() : array
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
    public function hot_brands_data_from_database() : array
    {
        $data = array();
        $forums = array('ACER', 'ASUS', 'SAMSUNG', 'SONY', 'XIAOMI');
        foreach($forums as $forum) {
            $this->CI->Analysis_Mobile01_Model->forums = $forum;
            $this->CI->Analysis_Mobile01_Model->authur_date = array();
            $this->CI->Analysis_Mobile01_Model->authur_date[] = $this->expire_date;
            $this->CI->Analysis_Mobile01_Model->authur_date[] = $this->now;
            $this->CI->Analysis_Mobile01_Model->get_forum_post_num();
            $data[] = $this->CI->Analysis_Mobile01_Model->data_table;
        }

        $params = array(
            'forums' => $forums,
            'data' => $data
        );

        $key = "hot_brands_data";
        $this->CI->cache->redis->save($key, $params, 300);

        return $params;
    }

    /**
     * 從快取取得各品牌每週發文數
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料])
     */
    public function hot_brands_data_from_cache() : array
    {
        $key = "hot_brands_data";
        $params = $this->CI->cache->redis->get($key);

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
    public function draw_hot_brands_chart(array $forums = array(), array $data = array())
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->ECharts->tooltip = array(
            'show' => true,
            'trigger' => 'axis',
            'axisPointer' => array(
                'type' => 'shadow'
            )
        );
        $this->ECharts->grid = array(
            'left' => '3%',
            'right' => '4%',
            'bottom' => '3%',
            'containLabel' => true
        );
        $this->ECharts->legend->data[] = '各品牌一週新發文數';
        $this->ECharts->xAxis[] = array(
            'type' => 'category',
            'data' => $forums
        );
        $this->ECharts->yAxis[] = array(
            'type' => 'value'
        );
        $this->ECharts->series[] = array(
            'name' => '各品牌一週新發文數',
            'type' => 'bar',
            'data' => $data
        );
        echo $this->ECharts->render('hot_brands');
    }

    /**
     * 取得各品牌每天發文數資料
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料], 'date_time_group' => [日期群組 YYYY-MM-DD HH:MM:SS])
     */
    public function hot_brands_by_date_data() : array
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
    public function hot_brands_by_date_data_from_database() : array
    {
        $date_time_group = array();

        for($i = (int) $this->CI->config->item('mobile01_expire_date'); $i > 0; $i--) {
            $date_time_group[] = date('Y-m-d', strtotime(date('Y-m-d') . ' 00:00:00') - 86400 * $i);
        }

        $forums = array('ACER', 'ASUS', 'SAMSUNG', 'SONY', 'XIAOMI');
        $data = array();

        foreach($forums as $forum) {
            foreach($date_time_group as $date_time) {
                $this->CI->Analysis_Mobile01_Model->forums = $forum;
                $this->CI->Analysis_Mobile01_Model->authur_date = array();
                $this->CI->Analysis_Mobile01_Model->authur_date[] = "{$date_time} 00:00:00";
                $this->CI->Analysis_Mobile01_Model->authur_date[] = "{$date_time} 23:59:59";
                $this->CI->Analysis_Mobile01_Model->get_forum_post_num();
                $data[$forum][] = $this->CI->Analysis_Mobile01_Model->data_table;
            }
        }

        $params = array(
            'forums' => $forums,
            'data' => $data,
            'date_time_group' => $date_time_group
        );

        $key = "hot_brands_by_date_data";
        $this->CI->cache->redis->save($key, $params, 300);

        return $params;
    }

    /**
     * 從快取取得各品牌每天發文數
     * @return array array('forums' => [各品牌], 'data' => [各品牌資料], 'date_time_group' => [日期群組 YYYY-MM-DD HH:MM:SS])
     */
    public function hot_brands_by_date_data_from_cache() : array
    {
        $key = "hot_brands_by_date_data";
        $params = $this->CI->cache->redis->get($key);

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
    public function draw_hot_brands_by_date_chart(array $forums = array(), array $data = array(), array $date_time_group = array())
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->ECharts->title = array('text' => '各品牌每天新發文數');
        $this->ECharts->tooltip = array('trigger' => 'axis');
        $this->ECharts->legend->data = $forums;
        $this->ECharts->grid = array(
            'left' => '3%',
            'right' => '4%',
            'bottom' => '3%',
            'containLabel' => true
        );
        $this->ECharts->toolbox = array(
            'feature' => array('saveAsImage' => array())
        );
        $this->ECharts->xAxis[] = array(
            'type' => 'category',
            'boundaryGap' => false,
            'data' => $date_time_group
        );
        $this->ECharts->yAxis[] = array(
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

        $this->ECharts->series = $series;
        echo $this->ECharts->render('hot_brands_by_date');
    }
}
