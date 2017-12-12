<?php
require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;
class Membership extends REST_Controller {
    public function __construct() {
        parent::__construct();
        $this->lang->load('english_lang', 'english');
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
				$output['status']['status'] = $this->lang->line('success_status');
				$output['status']['status_code'] = $this->lang->line('code_200');
				$output['message'] = $this->lang->line('success_status');
				$output['response']['data'] = $data;
				$this->set_response($output, REST_Controller::HTTP_OK);	
			} else {
				$response['status']['status'] = $this->lang->line('failure_status');
				$response['status']['status_code'] = $this->lang->line('code_400');
				$response['message'] = $this->lang->line('record_not_found');
				$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);	
			}
		}else{
			$response['status']['status'] = $this->lang->line('failure_status');
			$response['status']['status_code'] = $this->lang->line('code_422');
			$response['message']['data'] = $this->form_validation->error_array();	
			$this->set_response($response, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);	
			}
    }
    
    /**
     * Get all memberships
     * URL : http://localhost/workchew/api/membership/getall_membership
     * METHOD: GET
     * RETURN: Json response 
     */
	public function getall_membership_get() {
				$response['status']['status'] = $this->lang->line('success_status');
				$response['status']['status_code'] = $this->lang->line('code_200');
				$response['message'] = $this->lang->line('success_status');
				$response['response']['data'] = $this->db->get('memberships')->result_array();
				$this->set_response($response, REST_Controller::HTTP_OK);	
	}
	
}
?>
