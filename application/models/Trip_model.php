<?php

/**
 * Created by PhpStorm.
 * User: AdeFulki
 * Date: 9/5/2017
 * Time: 5:19 PM
 */
class Trip_model extends CI_Model{
    private $table = 'trip';

    public function __construct(){
        parent::__construct();
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
        $this->db->where('ID_TRIP', $id);
        $return=$this->db->update($this->table, $data);
        return $return;
    }

    public function delete($id){
        $this->db->where('ID_TRIP', $id);
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
        $this->db->where('ID_TRIP', $id);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    public function isNotAvailable($id){
        $this->db->where('ID_TRIP', $id);
        $query = $this->db->get($this->table);
        if($query->num_rows() > 0)
            return false;
        else
            return true;
    }

    public function search($input){
        $query = $this->db->query("SELECT * FROM ".$this->table." WHERE MATCH (TITLE_TRIP,DESCRIPTION_TRIP,ADDRESS_DESTINATION) AGAINST ('$input' IN BOOLEAN MODE) ORDER BY COUNT_REQUEST DESC LIMIT 5");
        return $query->result();
    }

    public function getPopular(){
        $this->db->limit(5);
        $this->db->order_by("COUNT_REQUEST", "DESC");
        $query = $this->db->get($this->table);
        return $query->result();
    }
}