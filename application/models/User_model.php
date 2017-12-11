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
        return $this->db->select($select)->where($where)->get('users')->row_array();
    }
    
     public function update($data, $where) {
        $this->db->update('users', $data, $where);
        return $this->db->affected_rows();
    }
    
    public function adduser_subscription($insert){
		if($this->db->insert('users_subscription', $insert)){
			return $this->db->insert_id();
		}
	}
	
	public function getuser_subscription($id){
		$this->db->select('us.id AS suscription_id,us.status,us.start_date,us.end_date,us.check_in,u.email,u.first_name,u.last_name,m.name AS membershp_name,m.membership,m.valid_upto,m.price,m.detail');
		$this->db->from('users_subscription us');
		$this->db->join('users u', 'u.id = us.user_id', 'INNER');
		$this->db->join('memberships m', 'm.id = us.membership_id', 'INNER');
		$this->db->where('us.id',$id);
		$query = $this->db->get(); 
		return $query->row_array();
	}
	
	public function getusersall_subscription($where,$limit,$offset) {
        $this->db->select('us.id AS subscription_id,us.status,us.start_date,us.end_date,us.check_in,u.email,u.first_name,u.last_name,m.name AS membershp_name,m.membership,m.valid_upto,m.price,m.detail');
		$this->db->from('users_subscription us');
		$this->db->join('users u', 'u.id = us.user_id', 'INNER');
		$this->db->join('memberships m', 'm.id = us.membership_id', 'INNER');
		$this->db->where('us.user_id',$where);
		$this->db->limit($limit, $offset);
		$query = $this->db->get(); 
		return $query->result_array();
    }
    
    public function check_subscription($id){
		$this->db->select('*');
		$this->db->from('users_subscription');
		$this->db->order_by('id', 'DESC');
		$this->db->where('user_id',$id);
		$query = $this->db->get(); 
		return $query->row_array();
	}
	
	public function update_usersubscription($data, $where) {
        $this->db->update('users_subscription', $data, $where);
        return $this->db->affected_rows();
    }
    
	public function getallusers_subscription() {
		$this->db->select('us.id AS subscription_id,us.status,us.start_date,us.end_date,us.check_in,u.email,u.device_token,u.first_name,u.last_name,m.name AS membershp_name,m.membership,m.valid_upto,m.price,m.detail');
		$this->db->from('users_subscription us');
		$this->db->join('users u', 'u.id = us.user_id', 'INNER');
		$this->db->join('memberships m', 'm.id = us.membership_id', 'INNER');
		$query = $this->db->get(); 
		return $query->result_array();
    }
}
?>
