<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analysis_Mobile01_Log_Model extends CI_Model
{

    protected $table;

    public $id;
    public $forums;
    public $status_code;
    public $run_at;

    public $data_table;

    public function __construct()
    {
        parent::__construct();
        $this->table = 'Analysis_Mobile01_Log';
    }

    public function add() : bool
    {
        $data = array(
            'forums' => $this->forums,
            'status_code' => $this->status_code,
            'run_at' => $this->run_at
        );

        if(!$this->db->insert($this->table, $data))
            return false;

        $this->data_table = $this->db->insert_id();

        return true;
    }
}
