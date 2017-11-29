<?php

class Bussiness_model extends CI_Model {

    public function __construct() {
    }
    
    public function AddRestaurent($data,$category_data,$hours_data,$reviews_data) {
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
                $this->business_hours($id,$hours_data);
                $this->business_reviews($id,$reviews_data);
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
	
    public function business_hours($id,$hours_data){
		if(!empty($hours_data['hours'])){
			//print_r($hours_data['hours'][0]);die();
			   $this->db->delete('bussiness_hours', array('businesses_id' => $id));
			   foreach($hours_data['hours'][0]['open'] as $hours){
				$hours = array(
                    'businesses_id' => $id,
                    'is_overnight' => $hours['is_overnight'],
                    'start' => $hours['start'],
                    'end' => $hours['end'],
                    'day' => $hours['day'],
                    'hours_type' => $hours_data['hours'][0]['hours_type'],
                    'is_open_now' => $hours_data['hours'][0]['is_open_now']
                );    
                $result = $this->db->insert('bussiness_hours', $hours);   
			 }
                return true; 
		} else {
                return false;
        }	
	}
	
    public function business_reviews($id,$reviews_data){
		if(!empty($reviews_data)){
			   $this->db->delete('bussiness_reviews', array('businesses_id' => $id));
			   foreach($reviews_data['reviews'] as $reviews){
				$reviews = array(
                    'businesses_id' => $id,
                    'text' => $reviews['text'],
                    'rating' => $reviews['rating'],
                    'user_image_url' => $reviews['user']['image_url'],
                    'user_name' => $reviews['user']['name'],
                    'time_created' => $reviews['time_created']
                );    
                $result = $this->db->insert('bussiness_reviews', $reviews);   
			 }
                return true; 
		} else {
                return false;
        }	
	}
	
	public function get_result($where = array(), $select = '*',$or_where = array()) {
        return $this->db->select($select)->where($where)->or_where($or_where)->get('businesses')->result_array();
    }
    
    public function get_business_detail($where = array(), $select = '*') {
		$bussiness_data = array();
        $result = $this->db->select($select)->where($where)->get('businesses')->row_array();
		if($result){
			$result['hours'] = $this->db->select('*')->where('businesses_id', $result['id'])->get('bussiness_hours')->result_array();
			$result['categories'] = $this->db->select('*')->where('business_id', $result['id'])->get('categories')->result_array();
			$result['reviews'] = $this->db->select('*')->where('businesses_id', $result['id'])->get('bussiness_reviews')->result_array();
		}
		$bussiness_data[] = $result;
		return $bussiness_data;
    }
}
?>
