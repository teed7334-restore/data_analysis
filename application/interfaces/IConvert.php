<?php
defined('BASEPATH') OR exit('No direct script access allowed');

interface IConvert
{
    /**
     * 新增搜尋引擎 Schema
     * @return void
     */
    public function createSchema();

    /**
     * 移除搜尋引擎 Schema
     * @return void
     */
    public function dropSchema();

    /**
     * 將資料庫資料倒去搜尋引擎
     * @return void
     */
    public function dumpDatabase2Elasticsearch();
}
