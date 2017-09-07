<?php

/**
 * Created by PhpStorm.
 * User: AdeFulki
 * Date: 9/5/2017
 * Time: 5:19 PM
 */
class User_model extends CI_Model{
    private $table = 'USER';

    public function __construct(){
        parent::__construct();
        $this->load->database();
    }

    public function add($data){
        $return = $this->db->insert($this->table, $data);
        if ((bool) $return === TRUE) {
            return $this->db->insert_id();
        } else {
            return $return;
        }

    }

    public function update($id, $data){
        $this->db->where('ID_USER', $id);
        $return=$this->db->update($this->table, $data);
        return $return;
    }

    public function delete($id){
        $this->db->where('ID_USER', $id);
        $this->db->delete($this->table);
    }

    public function getAll() {
        $query = $this->db->get($this->table);
        return $query->result();
    }

    public function getCount() {
        return $this->db->count_all($this->table);
    }

    public function getRecordsById($id) {
        $this->db->where('ID_USER', $id);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    public function isNotAvailable($id){
        $this->db->where('ID_USER', $id);
        $query = $this->db->get($this->table);
        if($query->num_rows() > 0)
            return false;
        else
            return true;
    }
}