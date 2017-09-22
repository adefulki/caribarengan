<?php

/**
 * Created by PhpStorm.
 * User: AdeFulki
 * Date: 9/5/2017
 * Time: 5:19 PM
 */
class Request_model extends CI_Model{
    private $table = 'request';

    public function __construct(){
        parent::__construct();
    }

    public function add($data){
        $return = $this->db->insert($this->table, $data);
        if ((bool) $return === FALSE)
            return false;
        else
            return true;

    }

    public function update($id, $data){
        $this->db->where('ID_REQUEST', $id);
        $return=$this->db->update($this->table, $data);
        return $return;
    }

    public function delete($id){
        $this->db->where('ID_REQUEST', $id);
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
        $this->db->where('ID_REQUEST', $id);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    public function getRecordsByUserId($id) {
        $this->db->where('ID_USER', $id);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    public function getRecordsByUserIdTripId($idUser, $idTrip) {
        $this->db->where('ID_TRIP', $idTrip);
        $this->db->where('ID_USER', $idUser);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    public function isNotAvailable($idTrip, $idUser){
        $this->db->where('ID_TRIP', $idTrip);
        $this->db->where('ID_USER', $idUser);
        $query = $this->db->get($this->table);
        if($query->num_rows() > 0)
            return false;
        else
            return true;
    }
}