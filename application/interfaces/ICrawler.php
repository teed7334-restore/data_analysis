<?php
defined('BASEPATH') OR exit('No direct script access allowed');

interface ICrawler
{
    /**
     * 將所有結構化資料寫入資料庫
     * @return void
     */
    public function saveAllAnalysisData();

    /**
     * 將討論區資料結構化
     * @param  string  $forums 品牌
     * @return void
     */
    public function analysisWebsiteData(string $forums = 'ACER');

    /**
     * 取得網頁資料
     * @return void
     */
    public function getWebsiteData();

    /**
     * 取得網頁資料共用元件
     * @param  string $url    網址
     * @param  string $method POST/GET
     * @return array          array('status' => [HTTP訊息代碼], 'html' => [網頁HTML])
     */
    public function getContent(string $url = '', string $method = 'GET') : array;
}
