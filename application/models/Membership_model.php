<?php

class Membership_model extends CI_Model {

    public function __construct() {
    }
    
	public function get_row($where = array(), $select = '*') {
        return $this->db->select($select)->where($where)->get('memberships')->row();
    }
    
     public function update($data, $where) {
        $this->db->update('memberships', $data, $where);
        return $this->db->affected_rows();
    }
}
?>
