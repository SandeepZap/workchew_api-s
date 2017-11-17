<?php
require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;
class Membership extends REST_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('membership_model');
    }

    /**
     * Get memership data by membership name
     * URL : http://localhost/workchew/api/membership/get_membership_byname
     * METHOD: POST
     * PARAMS: membership
     * RETURN: Json response 
     */
    public function get_membership_byname_post() {
        $membership = $this->post('membership');
        $this->form_validation->set_rules('membership', 'Membership', 'required');
        if ($this->form_validation->run()) {
			$data = $this->membership_model->get_row(array('membership' => $membership), array('id','name', 'membership','valid_upto','price'));
			if($data) {
				$output['status'] = 'success';
				$output['status_code'] = '200';
				$output['response'] = $data;
				$this->set_response($output, REST_Controller::HTTP_OK);	
			} else {
				$response['status'] = 'failure';
				$response['status_code'] = '400';
				$response['response'] = 'Record Not found';	
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);	
			}
		}else{
			   $this->set_response(array("status" => "failure", "status_code" => "422", "response" => $this->form_validation->error_array()), REST_Controller::HTTP_UNPROCESSABLE_ENTITY);		
			}
    }
    
    /**
     * Get all memberships
     * URL : http://localhost/workchew/api/membership/getall_membership
     * METHOD: GET
     * PARAMS: 
     * RETURN: Json response 
     */
	public function getall_membership_get() {
		 $this->set_response(array("status" => "success", "status_code" => "200", "response" => $this->db->get('memberships')->result()), REST_Controller::HTTP_OK);
	}
	
}
?>
