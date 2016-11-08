<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include(dirname(dirname(__FILE__)) . 'Base.php');
include(dirname(dirname((dirname(__FILE__)))) . '/interfaces/IConvert.php');

/**
 * 處理與繪制Mobile01分析後之資料與圖表
 */
class Mobile01 extends Base implements IConvert {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 新增搜尋引擎 Schema
     * @return void
     */
    public function createSchema()
    {
        $params = array(
            'index' => 'data-analysis',
            'body' => array(
                'mappings' => array(
                    'Analysis_Mobile01' => array(
                        'properties' => array(
                            'id' => array('type' => 'integer'),
                            'forums' => array('type' => 'string'),
                            'subject' => array('type' => 'string'),
                            'hot' => array('type' => 'integer'),
                            'reply' => array('type' => 'integer'),
                            'authur' => array('type' => 'string'),
                            'authur_date' => array(
                                'type' => 'date',
                                'format' => 'yyyy-MM-dd HH:mm:ss'
                            ),
                            'letest_reply_date' => array(
                                'type' => 'date',
                                'format' => 'yyyy-MM-dd HH:mm:ss'
                            ),
                            'mobile01_forums_code' => array('type' => 'integer'),
                            'mobile01_thread_code' => array('type' => 'integer')
                        )
                    )
                )
            )
        );

        $this->Elasticsearch->indices()->create($params);
    }

    /**
     * 移除搜尋引擎 Schema
     * @return void
     */
    public function dropSchema()
    {
        $params = array('index' => 'data-analysis');
        $this->Elasticsearch->indices()->delete($params);
    }

    /**
     * 將資料庫資料倒去搜尋引擎
     * @return void
     */
    public function dumpDatabase2Elasticsearch()
    {
        $this->dropSchema();
        $this->createSchema();

        $this->CI->load->model('Analysis_Mobile01_Model');
        $this->CI->Analysis_Mobile01_Model->dump_all_data();
        $data = $this->CI->Analysis_Mobile01_Model->data_table;
        foreach($data as $index => $values)
        {
            $params = array(
                'index' => 'data-analysis',
                'type' => 'Analysis_Mobile01',
                'id' => $index,
                'body' => $values
            );

            $this->Elasticsearch->index($params);
        }

    }
}
