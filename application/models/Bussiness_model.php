<?php

class Bussiness_model extends CI_Model {

    public function __construct() {
    }
    
    public function AddRestaurent($data,$category_data) {
       $query = $this->db->get_where('businesses', array('businesses_id' => $data['businesses_id']));
        if ($query->num_rows() > 0) {
          $business_data = array('name' =>  $data['name'], 'image_url' => $data['image_url'],'review_count'=> $data['review_count'],'rating'=> $data['rating'],'latitude'=> $data['latitude'],'longitude'=> $data['longitude'],'price'=> $data['price'],'address1'=> $data['address1'],'address2'=> $data['address2'],'city'=> $data['city'],'zip_code'=> $data['zip_code'],'country'=> $data['country'],'state'=> $data['state'],'phone'=> $data['phone'],'distance'=> $data['distance'],'updated_at' => date('Y-m-d H:i:s'));
            $this->db->where('businesses_id', $data['businesses_id']);
            $this->db->update('businesses', $business_data);
            if ($this->db->affected_rows()) {
                return true;
            } else {
                return false;
            }
        } else {
            $result = $this->db->insert('businesses', $data);
            if ($result > 0) {
                $id = $this->db->insert_id();
                $this->categories($id,$category_data);
                return true;
            } else {
                return false;
            }
        }
    }
    
    public function categories($id,$category_data){
		if(!empty($category_data)){
			   $this->db->delete('categories', array('business_id' => $id));
			   foreach($category_data['categories'] as $category){
				$category = array(
                    'business_id' => $id,
                    'alias' => $category['alias'],
                    'title' => $category['title']
                );       
                $result = $this->db->insert('categories', $category);   
			 }
                return true; 
		} else {
                return false;
        }	
	}
	
	public function get_result($where = array(), $select = '*',$or_where = array()) {
        return $this->db->select($select)->where($where)->or_where($or_where)->get('businesses')->result_array();
    }
    
    public function get_row($where = array(), $select = '*') {
        return $this->db->select($select)->where($where)->get('businesses')->row_array();
    }
}
?>
