<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once(dirname(dirname(__FILE__)) . '/Base.php');
include_once(dirname(dirname((dirname(__FILE__)))) . '/interfaces/IConvert.php');

/**
 * 處理與繪制Mobile01分析後之資料與圖表
 */
class Mobile01_Detail_Convert extends Base implements IConvert {

    protected $index;
    protected $type;

    public $id;
    public $mobile01_forums_code;
    public $mobile01_thread_code;
    public $html;

    public function __construct()
    {
        parent::__construct();
        $this->index = 'mobile01_detail';
        $this->type = 'Crawler';
    }

    /**
     * 新增搜尋引擎 Schema
     * @return void
     */
    public function createSchema()
    {
        $params = array(
            'index' => $this->index,
            'body' => array(
                'mappings' => array(
                    "{$this->type}" => array(
                        'properties' => array(
                            'mobile01_forums_code' => array('type' => 'integer'),
                            'mobile01_thread_code' => array('type' => 'integer'),
                            'html' => array('type' => 'string')
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
        $params = array('index' => $this->index);
        $this->Elasticsearch->indices()->delete($params);
    }

    /**
     * 將資料庫資料倒去搜尋引擎
     * @return void
     */
    public function dumpDatabase2Elasticsearch()
    {
        $params = array(
            'index' => $this->index,
            'type' => $this->type,
            'id' => $this->id,
            'body' => array(
                'mobile01_forums_code' => $this->mobile01_forums_code,
                'mobile01_thread_code' => $this->mobile01_thread_code,
                'html' => $this->html
            )
        );

        $this->Elasticsearch->index($params);
    }
}
