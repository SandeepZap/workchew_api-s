<?php
require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;
class Business extends REST_Controller {
    public function __construct() {
        parent::__construct();
        $this->lang->load('english_lang', 'english');
        $this->load->model('bussiness_model');
    }
    
    public function make_api_call($url){
		$access_token = 'f_GMjKlvOfRR8vY24Z0sq7qpZCJwrGkNBO49hR8g8Y4fYygYIJVr1Pnx3YMtBMoP9_tkHKRx4hXftzOui8z3KUH7RTBtumdhvOlxXeIToTdCiO_rQ5eRAVZmBm4bWnYx';
                $opts = array(
                    'http'=>array(
                        'ignore_errors' => TRUE,
                        'method'=>"GET",
                        'header'=>"Authorization: Bearer ". $access_token."\r\n"
                    ),
                     "ssl"=>array(
						"verify_peer"=>false,
						"verify_peer_name"=>false,
					),
                );
                $context = stream_context_create($opts);
                $json = file_get_contents($url, false, $context);
                $response_data = json_decode($json, TRUE);
				return $response_data;
	}

    /**
     * Get Business from yelp and sae into database
     * URL : http://localhost/workchew/index.php/api/business/get_bussiness
     * METHOD: POST
     * PARAMS: membership
     * RETURN: Json response 
     */
   public function get_bussiness_post(){
	   $this->form_validation->set_rules('location', 'Location', 'required');
		         if ($this->form_validation->run()) {
	   $location = $this->post('location');
		 $url = "https://api.yelp.com/v3/businesses/search?term=restaurants&location=".$location;
         $response_data = $this->make_api_call($url);
    foreach ($response_data['businesses'] as $restaurants) {
		//Get Hours for the business
			$hours_url = "https://api.yelp.com/v3/businesses/".$restaurants['id'];
			$hours_response = $this->make_api_call($hours_url);
		// Get reviews for the business
			$reviews_url = "https://api.yelp.com/v3/businesses/".$restaurants['id']."/reviews";
			$review_response = $this->make_api_call($reviews_url);
			
        	$restaurants_data = array('businesses_id'=>$restaurants['id'],
           'name'=>$restaurants['name'],
           'image_url'=>$restaurants['image_url'],
           'is_closed'=>$restaurants['is_closed'],
           'review_count'=>$restaurants['review_count'],
           'rating'=>$restaurants['rating'],
           'latitude'=>$restaurants['coordinates']['latitude'],
           'longitude'=>$restaurants['coordinates']['longitude'],
           'price'=> isset($restaurants['price']),
           'address1'=>$restaurants['location']['address1'],
           'address2'=>$restaurants['location']['address2'],
           'city'=>$restaurants['location']['city'],
           'zip_code'=>$restaurants['location']['zip_code'],
           'country'=>$restaurants['location']['country'],
           'state'=>$restaurants['location']['state'],
           'phone'=>$restaurants['phone'],
           'distance'=>$restaurants['distance'],
           'seats_available'=> '30',
           'discount'=> '15',
        );
        $category_data = array('categories'=>$restaurants['categories']); 
        $hours_data = array('hours'=> (isset($hours_response['hours'])) ? $hours_response['hours'] : ''); 
        $reviews_data = array('reviews'=>$review_response['reviews']); 
        $result = $this->bussiness_model->AddRestaurent($restaurants_data,$category_data,$hours_data,$reviews_data);
}
         if($result){
				$response['status']['status'] = $this->lang->line('success_status');
				$response['status']['status_code'] = $this->lang->line('code_200');
				$response['message'] = 'Bussiness Added successfully';
				$response['response']['data'] = 'Bussiness Added successfully';
				$this->set_response($response, REST_Controller::HTTP_OK);
			
		}else{
			$response['status']['status'] = $this->lang->line('failure_status');
			$response['status']['status_code'] =  $this->lang->line('code_500');
			$response['message'] = $this->lang->line('Internal_server_error');
			$response['response']['data'] = 'Bussiness not Added successfully';
			$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
	}else{
			$response['status']['status'] = $this->lang->line('failure_status');
			$response['status']['status_code'] = $this->lang->line('code_422');
			$response['message']["data"] = $this->form_validation->error_array();	
			$this->set_response($response, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
		
	}      
}
	 
	 
	 
	  /**
     * Get Business from database according to location
     * URL : http://localhost/workchew/index.php/api/business/get_bussiness_Bylocation
     * METHOD: POST
     * PARAMS: location,log,lat
     * RETURN: Json response 
     */
	 
	  public function get_bussiness_Bylocation_post(){
		  $this->form_validation->set_rules('location', 'Location', 'required');
		         if ($this->form_validation->run()) {
					 $location = $this->post('location');
					 $longitude = $this->post('longitude');
					 $latitude = $this->post('latitude');
					 $data = $this->bussiness_model->get_result(array('city' => $location),array('*'),array('longitude' => $longitude,'	latitude' => $latitude));
					 $output['status']['status'] = $this->lang->line('success_status');
					 $output['status']['status_code'] = $this->lang->line('code_200');
					 $output['message'] =  $this->lang->line('success_status');
					 $output['response']['data'] = $data;
					 $this->set_response($output, REST_Controller::HTTP_OK);   
					 
				 }else{
					 	$response['status']['status'] = $this->lang->line('failure_status');
                        $response['status']['status_code'] = $this->lang->line('code_422');
                        $response['message']["data"] = $this->form_validation->error_array();	
						$this->set_response($response, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);	
			}
		  
	  }
	  
	   /**
     * Get Business from database according to businessid
     * URL : http://localhost/workchew/index.php/api/business/get_bussiness_detail
     * METHOD: POST
     * PARAMS: businesses_id
     * RETURN: Json response 
     */
	 
	  public function get_bussiness_detail_post(){
		  $this->form_validation->set_rules('businesses_id', 'Businesses', 'required');
		   if ($this->form_validation->run()) {
				$businesses_id = $this->post('businesses_id');
				$data = $this->bussiness_model->get_business_detail(array('businesses_id' => $businesses_id), array('*'));
					 if(!empty($data)){     
							$response['status']['status'] = $this->lang->line('success_status');
							$response['status']['status_code'] = $this->lang->line('code_200');
							$response['message'] =  $this->lang->line('success_status');
							$response['response']['data'] = $data;
							$this->set_response($response, REST_Controller::HTTP_OK);
					 }else{
							$response['status']['status'] = $this->lang->line('failure_status');
							$response['status']['status_code'] = $this->lang->line('code_400');
							$response['message'] = $this->lang->line('Invalid_id');
							$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST); 
					 }
		   }else{
					 	$response['status']['status'] = $this->lang->line('failure_status');
                        $response['status']['status_code'] = $this->lang->line('code_422');
                        $response['message']["data"] = $this->form_validation->error_array();	
						$this->set_response($response, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);	
			}
	  }
   
	
}
?>
