<?php

class User_model extends CI_Model {

    public function __construct() {
    }
    public function login($email, $password) {
        $this->db->select('*');
  $this->db->from('users');
  $this->db->where('email', $email);
  $this->db->where('password', $password);
  $query = $this->db->get();
        if ($query->num_rows() == 1) {
            $result = $query->result();
            return $result[0]->id;
        }
        return false;
    }
    
    
    public function signup_user($insert){
		if($this->db->insert('users', $insert)){
          return $this->db->insert_id();
        }
	}
	
	public function get_row($where = array(), $select = '*') {
        return $this->db->select($select)->where($where)->get('users')->row();
    }
    
     public function update($data, $where) {
        $this->db->update('users', $data, $where);
        return $this->db->affected_rows();
    }
}
?>
