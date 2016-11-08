<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analysis_Mobile01_Log_Model extends CI_Model
{

    //資料表名稱
    protected $table;

    //資料表Schema
    public $id;
    public $forums;
    public $status_code;
    public $run_at;

    //存取回傳資料
    public $data_table;

    public function __construct()
    {
        parent::__construct();
        $this->table = 'Analysis_Mobile01_Log';
    }

    /**
     * 新增資料表
     * @return bool
     */
    public function add() : string
    {
        $data = array(
            'forums' => $this->forums,
            'status_code' => $this->status_code,
            'run_at' => $this->run_at
        );

        if(!$this->db->insert($this->table, $data))
            return '';

        $this->data_table = $this->db->insert_id();

        return $this->db->last_query();
    }
}
