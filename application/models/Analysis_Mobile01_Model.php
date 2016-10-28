<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analysis_Mobile01_Model extends CI_Model
{

    protected $table;

    public $id;
    public $forums;
    public $subject;
    public $hot;
    public $reply;
    public $authur;
    public $authur_date;
    public $latest_replay_date;
    public $mobile01_forums_code;
    public $mobile01_thread_code;

    public $data_table;

    public function __construct()
    {
        parent::__construct();
        $this->table = 'Analysis_Mobile01';
    }

    public function add() : bool
    {
        $data = array(
            'forums' => $this->forums,
            'subject' => $this->subject,
            'hot' => $this->hot,
            'reply' => $this->reply,
            'authur' => $this->authur,
            'authur_date' => $this->authur_date,
            'latest_replay_date' => $this->latest_replay_date,
            'mobile01_forums_code' => $this->mobile01_forums_code,
            'mobile01_thread_code' => $this->mobile01_thread_code
        );

        if(!$this->db->insert($this->table, $data))
            return false;

        $this->data_table = $this->db->insert_id();

        return true;
    }

    public function edit_have_data() : bool
    {
        $this->db->set('hot', $this->hot);
        $this->db->set('reply', $this->reply);
        $this->db->set('latest_replay_date', $this->latest_replay_date);
        $this->db->where('id', $this->id);
        $this->db->update($this->table);
        $this->data_table = $this->db->affected_rows();
        return true;
    }

    public function get_have_data() : bool
    {
        $this->db->select('id');
        $this->db->from($this->table);
        $this->db->where('forums', $this->forums);
        $this->db->where('mobile01_forums_code', $this->mobile01_forums_code);
        $this->db->where('mobile01_thread_code', $this->mobile01_thread_code);
        $this->data_table = $this->db->get()->result_array();
        return true;
    }

    public function get_forum_post_num() : bool
    {
        $this->db->from($this->table);
        $this->db->where('authur_date > ', $this->authur_date[0]);
        $this->db->where('authur_date < ', $this->authur_date[1]);
        $this->db->where('forums', $this->forums);
        $this->data_table = $this->db->count_all_results();
        return true;
    }
}
