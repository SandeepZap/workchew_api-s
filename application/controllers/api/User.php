<?php
require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
require_once APPPATH . '/libraries/SignatureInvalidException.php';
use \Firebase\JWT\JWT;
class User extends REST_Controller {
    public function __construct() {
        parent::__construct();
        $this->lang->load('english_lang', 'english');
        $this->load->model('user_model');
    }

    /**
     * User Login API
     * URL : http://localhost/workchew/index.php/api/user/login
     * METHOD: POST
     * PARAMS: email, password
     * RETURN: Json response 
     */
    public function login_post() {
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');
	if ($this->form_validation->run()) {
			$email = $this->post('email');
			$password = md5($this->post('password'));
			$id = $this->user_model->login($email,$password);
		if($id) {
				$subscription = $this->user_model->check_subscription($id);
				$user = $this->user_model->get_row(array('id' => $id), array('first_name', 'last_name' ,'id','email','username'));
				$token['id'] = $id;
				$token['email'] = $email;
				$date = new DateTime();
				$token['iat'] = $date->getTimestamp();
				$response['status']['status'] = $this->lang->line('success_status');
				$response['status']['status_code'] = $this->lang->line('code_200');
				$response['message'] =  $this->lang->line('login_successfull');
			if(!empty($subscription)){
				  $end_date = $subscription['end_date'];
				  $current_date = date('Y-m-d H:i:s');
				  if($end_date < $current_date){
					$update_subscription = $this->user_model->update_usersubscription(array('status' => '0'), array('id' => $subscription['id']));
					$subscription = $this->user_model->check_subscription($id);
				  }
				$response['response']['data'] = array_merge($subscription,$user);
			}else{
				$response['response']['data'] = $user;
				$response['response']['data']['status'] = '0';
			}
							
				$response['response']['data']['id_token'] = JWT::encode($token,MY_SECRET_KEY);
				$this->set_response($response, REST_Controller::HTTP_OK); 
		}else{
						$response['status']['status'] = $this->lang->line('failure_status');
                        $response['status']['status_code'] = $this->lang->line('code_401');
                        $response['message'] = $this->lang->line('Invalid');
						$this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);	
			}
	}else{
						$response['status']['status'] = $this->lang->line('failure_status');
                        $response['status']['status_code'] = $this->lang->line('code_422');
                        $response['message']["data"] = $this->form_validation->error_array();	
						$this->set_response($response, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);	
	}
}
    
    /**
     * User Signup API
     * URL : http://localhost/workchew/index.php/api/user/signup
     * METHOD: POST
     * PARAMS: email, password, first_name ,last_name , username 
     * RETURN: Json response 
     */
	public function signup_post() {
		 $this->form_validation->set_rules('email', 'Email',  'required|valid_email|is_unique[users.email]');
		 $this->form_validation->set_rules('first_name', 'First Name', 'required');
		 $this->form_validation->set_rules('last_name', 'Last Name', 'required');
		 $this->form_validation->set_rules('username', 'Username', 'required');
		 $this->form_validation->set_rules('password', 'Password', 'required');
		if ($this->form_validation->run() == true){
			$insert = array(
			  'email' => $this->post('email'),
			  'first_name' => $this->post('first_name'),
			  'last_name' => $this->post('last_name'),
			  'username' => $this->post('username'),
			  'password' => md5($this->post('password')),
			);
			$result = $this->user_model->signup_user($insert);
						if ($result) {
							$user = $this->user_model->get_row(array('id' => $result), array('first_name', 'last_name','email','username'));
							$response['status']['status'] = $this->lang->line('success_status');
							$response['status']['status_code'] = $this->lang->line('code_201');
							$response['message'] = $this->lang->line('register_successfull');
							$response['response']['data'] = $user;
							$this->set_response($response, REST_Controller::HTTP_CREATED);	
						}else{
							$response ['status']['status'] = $this->lang->line('failure_status');
							$response['status']['status_code'] = $this->lang->line('code_500');
							$response['message'] = $this->lang->line('Internal_server_error');
							$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);	
						}		
		}else{
							$response ['status']['status'] = $this->lang->line('failure_status');
							$response['status']['status_code'] = $this->lang->line('code_422');
							$response['message']['data'] = $this->form_validation->error_array();	
							$this->set_response($response, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);	
		}
	}
	
	 /**
     * User Forgot Password API
     * URL : http://localhost/workchew/index.php/api/user/forgot_password
     * METHOD: POST
     * PARAMS: email
     * RETURN: Json response. Send password in mail 
     */	
	public function forgot_password_post(){
       $this->form_validation->set_rules('email', 'Email', 'trim|required');
			if ($this->form_validation->run()) {
                    /*
                     * check if user exists in database with this email address
                     */
                    $user = $this->user_model->get_row(array('email' => $this->post('email')), array('first_name', 'last_name' ,'id'));
                    if (!empty($user)) {
                                $passwordplain = "";
								$passwordplain  = rand(999999999,9999999999);
								$newpass = md5($passwordplain);
                        $saved = $this->user_model->update(array(
                            'password' => $newpass
                                ), array('email' =>  $this->post('email')));
                        if ($saved) {
                            $data_vars = array(
                                'first_name' => $user['first_name'],
                                'email' => $this->post('email'),
                                'password' => $passwordplain
                            );
                            $this->load->library('common');
                            if($this->common->send_mail($this->post('email'), 'Workchew: Forgot Password', 'forgot-password-email', $data_vars, 'email')){
								$response['status']['status'] = $this->lang->line('success_status');
								$response['status']['status_code'] = $this->lang->line('code_200');
								$response['message'] = 'Please check your email for your password';
								$response['response']['data'] = 'Please check your email for your password';
								$this->set_response($response, REST_Controller::HTTP_OK);
							}else{
								$response['status']['status'] = $this->lang->line('failure_status');
								$response['status']['status_code'] =  $this->lang->line('code_500');
								$response['message'] = 'Email not Sent';
								$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
							}	
                        } else {
							$response['status']['status'] = $this->lang->line('failure_status');
							$response['status']['status_code'] = $this->lang->line('code_500');
							$response['message'] = $this->lang->line('Internal_server_error');
							$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    } else {
							$response['status']['status'] = $this->lang->line('failure_status');
							$response['status']['status_code'] = $this->lang->line('code_400');
							$response['message'] = $this->lang->line('Not_found');
							$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
                    }
                } else {
							$response['status']['status'] = $this->lang->line('failure_status');
							$response['status']['status_code'] = $this->lang->line('code_422');
							$response['message']['data'] = $this->form_validation->error_array();	
							$this->set_response($response, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
                }
	}
	
	/**
     * User Login using social API
     * URL : http://localhost/workchew/index.php/api/user/social_login
     * METHOD: POST
     * PARAMS: provider (facebook- uid,access_token | LinkedIn- access_token | Twitter- username)
     * RETURN: Json response
     */
     
	public function social_login_post(){
	$this->form_validation->set_rules('provider', 'Provider', 'required');	
    if ($this->form_validation->run()) {		
		if($this->post('provider') == 'facebook'){	
			/*
			* Login using facebook 
			*/
			$access_token = $this->post('access_token');
			$uid = $this->post('uid');
			$url= FACEBOOK_URL.$uid."/?access_token=".urlencode($access_token);
			$opts = array(
				'http' => array('ignore_errors' => true)
					);
			$context = stream_context_create($opts);
			$json = file_get_contents($url, false, $context);
			$json_response = json_decode($json, TRUE);
				if(isset($json_response['id'])){
					$get_user = $this->user_model->get_row(array('uid' => $this->post('uid')), array('first_name', 'last_name' ,'id','uid'));
						if(!empty($get_user)){
							$token['id'] = $get_user['id'];
							$token['uid'] = $get_user['uid'];
							$date = new DateTime();
							$token['iat'] = $date->getTimestamp();
							$response['status']['status'] = $this->lang->line('success_status');
							$response['status']['status_code'] = $this->lang->line('code_200');
							$response['message'] =  $this->lang->line('login_successfull');
							$subscription = $this->user_model->check_subscription($get_user['id']);
							if(!empty($subscription)){
								  $end_date = $subscription['end_date'];
								  $current_date = date('Y-m-d H:i:s');
								  if($end_date < $current_date){
										$update_subscription = $this->user_model->update_usersubscription(array('status' => '0'), array('id' => $subscription['id']));
										$subscription = $this->user_model->check_subscription($get_user['id']);
								  }
								$response['response']['data'] = array_merge($subscription,$get_user);
							}else{
								$response['response']['data'] = $get_user;
								$response['response']['data']['status'] = '0';
							}
							$response['response']['data']['id_token'] = JWT::encode($token,MY_SECRET_KEY);
							$this->set_response($response, REST_Controller::HTTP_OK);	
					 
						}else{
							$insert = array(
							  'email' => $this->post('email'),
							  'first_name' => $this->post('first_name'),
							  'last_name' => $this->post('last_name'),
							  'username' => $this->post('username'),
							  'uid' => $this->post('uid'),
							  'provider' => $this->post('provider')
							);
					 $result = $this->user_model->signup_user($insert);
						if ($result) {
							$user = $this->user_model->get_row(array('uid' => $this->post('uid')), array('first_name', 'last_name' ,'id','uid'));
								if(!empty($user)){	 
									$token['id'] = $user['id'];
									$token['uid'] = $user['uid'];
									$date = new DateTime();
									$token['iat'] = $date->getTimestamp();
									$response['status']['status'] = $this->lang->line('success_status');
									$response['status']['status_code'] = $this->lang->line('code_200');
									$response['message'] =  $this->lang->line('login_successfull');
									$response['response']['data'] = $user;
									$response['response']['data']['status'] = '0';
									$response['response']['data']['id_token'] = JWT::encode($token,MY_SECRET_KEY);
									$this->set_response($response, REST_Controller::HTTP_OK);	
								} else {
									$response['status']['status'] = $this->lang->line('failure_status');
									$response['status']['status_code'] = $this->lang->line('code_401');
									$response['message'] = $this->lang->line('Invalid');
									$this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);	
								}
				
						}else{
								$response['status']['status'] = $this->lang->line('failure_status');
								$response['status_code'] = $this->lang->line('code_500');
								$response['message'] = $this->lang->line('Internal_server_error');
								$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);	
						} 
						}
					}else{
						$response['status']['status'] = $this->lang->line('failure_status');
                        $response['status']['status_code'] = $this->lang->line('code_401');
                        $response['message'] = $this->lang->line('Unautorized_user'); 
						$this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
					}		
			} else if($this->post('provider') == 'twitter'){ 
				/*
				* Login using Twitter
				*/            
                $username =  $this->post('username');
                $url = TWITTER_URL.$username;
                $contents = @file_get_contents($url);
                if ($contents) {
                    $get_user = $this->user_model->get_row(array('username' => $this->post('username')), array('first_name', 'last_name' ,'id','username'));
                    if(!empty($get_user)){     
                            $token['id'] = $get_user['id'];
                            $date = new DateTime();
                            $token['iat'] = $date->getTimestamp();
                            $output['status']['status'] = $this->lang->line('success_status');
                            $output['status']['status_code'] = $this->lang->line('code_200');
                            $output['message'] =  $this->lang->line('login_successfull');
                            $subscription = $this->user_model->check_subscription($get_user['id']);
							if(!empty($subscription)){
								  $end_date = $subscription['end_date'];
								  $current_date = date('Y-m-d H:i:s');
								  if($end_date < $current_date){
										$update_subscription = $this->user_model->update_usersubscription(array('status' => '0'), array('id' => $subscription['id']));
										$subscription = $this->user_model->check_subscription($get_user['id']);
								  }
								$output['response']['data'] = array_merge($subscription,$get_user);
							}else{
								$output['response']['data'] = $get_user;
								$output['response']['data']['status'] = '0';
							}
                            $output['response']['data']['id_token'] = JWT::encode($token,MY_SECRET_KEY);
                            $this->set_response($output, REST_Controller::HTTP_OK);   
                    }else{
                            $insert = array(
                              'username' => $this->post('username'),
                              'email' => $this->post('email'),
                              'provider' => $this->post('provider')
                            );
                            $result = $this->user_model->signup_user($insert);
                            if ($result) {
                                $user = $this->user_model->get_row(array('username' => $this->post('username')), array('first_name', 'last_name' ,'id','username'));
                                    if(!empty($user)){     
                                        $token['id'] = $user['id'];
                                        $date = new DateTime();
                                        $token['iat'] = $date->getTimestamp();
                                        $output['status']['status'] = $this->lang->line('success_status');
                                        $output['status']['status_code'] = $this->lang->line('code_200');
                                        $output['message'] =  $this->lang->line('login_successfull');
                                        $output['response']['data'] = $user;
                                        $output['response']['data']['status'] = '0';
                                        $output['response']['data']['id_token'] = JWT::encode($token,MY_SECRET_KEY);
                                        $this->set_response($output, REST_Controller::HTTP_OK);   
                                    } else {
                                        $response['status']['status'] = $this->lang->line('failure_status');
                                        $response['status']['status_code'] = $this->lang->line('code_401');
                                        $response['message'] = $this->lang->line('Invalid');
                                        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);   
                                    }
                   
                            }else{
                                    $response['status']['status'] = $this->lang->line('failure_status');
                                    $response['status']['status_code'] = $this->lang->line('code_500');
                                    $response['message'] = $this->lang->line('Internal_server_error'); 
                                    $this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);   
                            }
                    }
                } else {
                    $response['status']['status'] = $this->lang->line('failure_status');
                    $response['status']['status_code'] = $this->lang->line('code_401');
                    $response['message'] = $this->lang->line('Invalid');
                    $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);           
                }    
            } else if($this->post('provider') == 'linkedin'){
				/*
				* Login using Linkedin
				*/
                $url= LINKEDIN_URL;
                $access_token = $this->post('access_token');
                $opts = array(
                    'http'=>array(
                        'ignore_errors' => TRUE,
                        'method'=>"GET",
                        'header'=>"Authorization: Bearer ". $access_token."\r\n" ."x-li-src: msdk\r\n"
                    )
                );
                $context = stream_context_create($opts);
                $json = file_get_contents($url, false, $context);
                $json_response = json_decode($json, TRUE);
                if(isset($json_response['id'])){
                    $get_user = $this->user_model->get_row(array('uid' => $json_response['id']), array('first_name', 'last_name' ,'id','uid'));
                        if(!empty($get_user)){     
                            $token['id'] = $get_user['id'];
                            $token['uid'] = $get_user['uid'];
                            $date = new DateTime();
                            $token['iat'] = $date->getTimestamp();
                            $output['status']['status'] = $this->lang->line('success_status');
                            $output['status']['status_code'] = $this->lang->line('code_200');
                            $output['message'] = $this->lang->line('login_successfull');
                            $subscription = $this->user_model->check_subscription($get_user['id']);
							if(!empty($subscription)){
								  $end_date = $subscription['end_date'];
								  $current_date = date('Y-m-d H:i:s');
								  if($end_date < $current_date){
										$update_subscription = $this->user_model->update_usersubscription(array('status' => '0'), array('id' => $subscription['id']));
										$subscription = $this->user_model->check_subscription($get_user['id']);
								  }
								$output['response']['data'] = array_merge($subscription,$get_user);
							}else{
								$output['response']['data'] = $get_user;
								$output['response']['data']['status'] = '0';
							}
                            $output['response']['data']['id_token'] = JWT::encode($token,MY_SECRET_KEY);
                            $this->set_response($output, REST_Controller::HTTP_OK);   
                     
                        }else{
                            $insert = array(
                              'email' => $this->post('email'),
                              'first_name' => $this->post('first_name'),
                              'last_name' => $this->post('last_name'),
                              'username' => $this->post('username'),
                              'uid' => $this->post('id'),
                              'provider' => $this->post('provider')
                            );
                     $result = $this->user_model->signup_user($insert);
                        if ($result) {
                            $user = $this->user_model->get_row(array('uid' => $this->post('id')), array('first_name', 'last_name' ,'id','uid'));
                                if(!empty($user)){     
                                    $token['id'] = $user['id'];
                                    $token['uid'] = $user['uid'];
                                    $date = new DateTime();
                                    $token['iat'] = $date->getTimestamp();
                                    $output['status']['status'] = $this->lang->line('success_status');
                                    $output['status']['status_code'] = $this->lang->line('code_200');
                                    $output['message'] =  $this->lang->line('login_successfull');
                                    $output['response']['data'] = $user;
                                    $output['response']['data']['status'] = '0';
                                    $output['response']['data']['id_token'] = JWT::encode($token,MY_SECRET_KEY);
                                    $this->set_response($output, REST_Controller::HTTP_OK);   
                                } else {
                                    $response['status']['status'] = $this->lang->line('failure_status');
                                    $response['status']['status_code'] = $this->lang->line('code_401');
                                    $response['message'] = $this->lang->line('Invalid'); 
                                    $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);   
                                }
               
                        }else{
                                $response['status']['status'] = $this->lang->line('failure_status');
                                $response['status']['status_code'] = $this->lang->line('code_500');
                                $response['message'] = $this->lang->line('Internal_server_error');
                                $this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);   
                        }
                        }
                    }else{
                        $response['status']['status'] = $this->lang->line('failure_status');
                        $response['status']['status_code'] = $this->lang->line('code_401');
                        $response['message'] = $this->lang->line('Unautorized_user'); 
                        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
                    }       
        }
       }else{
			$response['status']['status'] = $this->lang->line('failure_status');
			$response['status']['status_code'] = $this->lang->line('code_422');
			$response['message']['data'] = $this->form_validation->error_array();	
			$this->set_response($response, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
	}       
    
		}
		
	/**
     * User Change Password API
     * URL : http://localhost/workchew/index.php/api/user/change_password
     * METHOD: POST
     * PARAMS: email,id,new_password
     * RETURN: Json response.
     */	
	public function change_password_post(){
       $this->form_validation->set_rules('email', 'Email', 'trim|required');
       $this->form_validation->set_rules('id', 'User Id', 'trim|required');
       $this->form_validation->set_rules('new_password', 'Password', 'trim|required');
			if ($this->form_validation->run()) {
				/*
                     * check if user exists in database with details
                     */
                    $user = $this->user_model->get_row(array('email' => $this->post('email'),'id' => $this->post('id')), array('first_name', 'last_name' ,'id'));
                    if (!empty($user)) {
                                $passwordplain = $this->post('new_password');
								$newpass = md5($passwordplain);
                        $saved = $this->user_model->update(array(
                            'password' => $newpass
                                ), array('email' =>  $this->post('email'),'id' => $this->post('id')));
                        if ($saved) {
								$response['status']['status'] = $this->lang->line('success_status');
								$response['status']['status_code'] = $this->lang->line('code_200');
								$response['message'] = $this->lang->line('password_changed');
								$response['response']['data'] = $this->lang->line('password_changed');
								$this->set_response($response, REST_Controller::HTTP_OK);
                            
						}else{
							$response['status']['status'] = $this->lang->line('failure_status');
							$response['status']['status_code'] = $this->lang->line('code_500');
							$response['message'] = $this->lang->line('Internal_server_error');
							$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
						}
					}else{
							$response['status']['status'] = $this->lang->line('failure_status');
							$response['status']['status_code'] = $this->lang->line('code_400');
							$response['message'] = $this->lang->line('Not_found');
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
     * Add user subscription
     * URL : http://localhost/workchew/index.php/api/user/add_subscription
     * METHOD: POST
     * PARAMS: membership_id,start_date,valid_upto
     * RETURN: Json response 
     */
	public function add_subscription_post() {
		 $headers = $this->get_headers();
		if(isset($headers['token'])){
			$output = JWT::decode($headers['token'],MY_SECRET_KEY, array('HS256'));
			$decoded_array = (array) $output;
				if(isset($decoded_array['id'])){
					$user_id = $decoded_array['id']; 
						 $this->form_validation->set_rules('membership_id', 'Membership', 'required');
						 //$this->form_validation->set_rules('start_date', 'Date', 'required');
						if ($this->form_validation->run() == true){
							$user = $this->user_model->getuser_subscription($user_id);
								$start_date = date('Y-m-d H:i:s');
								$valid_upto = $this->post('valid_upto');
								$expires = strtotime('+'.$valid_upto.' days', strtotime($start_date));
								$date_diff=($expires-strtotime($start_date)) / 86400;
								$end_date = date('Y-m-d H:i:s', $expires);
								//$days_left = round($date_diff, 0);
							$insert = array(
							  'user_id' => $user_id,
							  'membership_id' => $this->post('membership_id'),
							  'status' => '1',
							  'start_date' => $start_date,
							  'end_date' => $end_date,
							);
							$id = $this->user_model->adduser_subscription($insert);
								if ($id) {
									$user = $this->user_model->getuser_subscription($id);
									$response['status']['status'] = $this->lang->line('success_status');
									$response['status']['status_code'] = $this->lang->line('code_201');
									$response['message'] = $this->lang->line('success_status');
									$response['response']['data'] = $user;
									$this->set_response($response, REST_Controller::HTTP_CREATED);	
								}else{
									$response ['status']['status'] = $this->lang->line('failure_status');
									$response['status']['status_code'] = $this->lang->line('code_500');
									$response['message'] = $this->lang->line('Internal_server_error');
									$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);	
								}		
						}else{
							$response ['status']['status'] = $this->lang->line('failure_status');
							$response['status']['status_code'] = $this->lang->line('code_422');
							$response['message']['data'] = $this->form_validation->error_array();	
							$this->set_response($response, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);	
						}
				}else{
					$response['status']['status'] = $this->lang->line('failure_status');
					$response['status']['status_code'] = $this->lang->line('code_400');
					$response['message'] = $this->lang->line('Not_found');
					$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
				}
		}else{
			$response['status']['status'] = $this->lang->line('failure_status');
			$response['status']['status_code'] = $this->lang->line('code_400');
			$response['message'] = $this->lang->line('token_not_found');
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	
	
	  /**
     * Get all users's subscription 
     * URL : http://localhost/workchew/index.php/api/user/get_user_subscription
     * METHOD: POST
     * PARAMS: user_id,limit,page
     * RETURN: Json response 
     */
	 
	  public function get_user_subscription_post(){
		 $headers = $this->get_headers();
		if(isset($headers['token'])){
			$output = JWT::decode($headers['token'],MY_SECRET_KEY, array('HS256'));
			$decoded_array = (array) $output;
				if(isset($decoded_array['id'])){
					$user_id = $decoded_array['id']; 
					$limit = $this->post('limit'); // set limit for pagination 
					$page_number = $this->post('page'); // set page number for pagination
					$offset = ($page_number - 1) * $limit; // set offset for pagination
					$data = $this->user_model->getusersall_subscription($user_id,$limit,$offset);
					 if(!empty($data)){   
						 $message = $this->lang->line('success_status');
					 }else{
						 $message = $this->lang->line('record_not_found');
					 }
					$response['status']['status'] = $this->lang->line('success_status');
					$response['status']['status_code'] = $this->lang->line('code_200');
					$response['message'] =  $message;
					$response['response']['data'] = $data;
					$this->set_response($response, REST_Controller::HTTP_OK);
				}else{
					$response['status']['status'] = $this->lang->line('failure_status');
					$response['status']['status_code'] = $this->lang->line('code_400');
					$response['message'] = $this->lang->line('Not_found');
					$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
				}
	   }else{
			$response['status']['status'] = $this->lang->line('failure_status');
			$response['status']['status_code'] = $this->lang->line('code_400');
			$response['message'] = $this->lang->line('token_not_found');
			$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
}
	  
	  /**
     * Get user subscription detail
     * URL : http://localhost/workchew/index.php/api/user/get_subscription_detail
     * METHOD: POST
     * PARAMS: suscription_id
     * RETURN: Json response 
     */
	 
	  public function get_subscription_detail_post(){
		  $this->form_validation->set_rules('suscription_id', 'Subscription', 'required');
		   if ($this->form_validation->run()) {
				$id = $this->post('suscription_id');
				 $subscription = $this->user_model->getuser_subscription($id);
					 if(!empty($subscription)){
							$response['status']['status'] = $this->lang->line('success_status');
							$response['status']['status_code'] = $this->lang->line('code_200');
							$response['message'] =  $this->lang->line('success_status');
							$response['response']['data'] = $subscription;
							$this->set_response($response, REST_Controller::HTTP_OK);   
					 }else{
							$response['status']['status'] = $this->lang->line('failure_status');
							$response['status']['status_code'] = $this->lang->line('code_400');
							$response['message'] = $this->lang->line('record_not_found');
							$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
					 }
		   }else{
					 	$response['status']['status'] = $this->lang->line('failure_status');
                        $response['status']['status_code'] = $this->lang->line('code_422');
                        $response['message']["data"] = $this->form_validation->error_array();	
						$this->set_response($response, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);	
			}
	  }
	  
	 /**
     * check user subscription status
     * URL : http://localhost/workchew/index.php/api/user/check_usersuscription
     * METHOD: POST
     * PARAMS: Headers token 
     * RETURN: Json response 
     */
	  
	    public function check_usersubscription_post(){
		$headers = $this->get_headers();
		if(isset($headers['token'])){
		$output = JWT::decode($headers['token'],MY_SECRET_KEY, array('HS256'));
		$decoded_array = (array) $output;
          if(isset($decoded_array['id'])){
			  $id = $decoded_array['id']; 
				 $subscription = $this->user_model->check_subscription($id);
			  if(!empty($subscription)){
				  $end_date = $subscription['end_date'];
				  $current_date = date('Y-m-d H:i:s');
				  $response['status']['status'] = $this->lang->line('success_status');
				  $response['status']['status_code'] = $this->lang->line('code_200');
				  if($end_date < $current_date){
					  $update_subscription = $this->user_model->update_usersubscription(array(
                            'status' => '0'
                                ), array('id' => $subscription['id']));
                            $subscription = $this->user_model->check_subscription($id);
							$response['message'] =  $this->lang->line('subscription_expired');		
				  }else{
							$response['message'] =  $this->lang->line('subscription_not_expired');
				  }
							$response['response']['data'] = $subscription;
							$this->set_response($response, REST_Controller::HTTP_OK); 
			  }else{
							$response['status']['status'] = $this->lang->line('failure_status');
							$response['status']['status_code'] = $this->lang->line('code_400');
							$response['message'] = $this->lang->line('record_not_found');
							$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
			  }
		  }else{
							$response['status']['status'] = $this->lang->line('failure_status');
							$response['status']['status_code'] = $this->lang->line('code_400');
							$response['message'] = $this->lang->line('Not_found');
							$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		  }
		}else{
							$response['status']['status'] = $this->lang->line('failure_status');
							$response['status']['status_code'] = $this->lang->line('code_400');
							$response['message'] = $this->lang->line('token_not_found');
							$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
		}
	}
	
	 /**
     * Function to get headers
     * RETURN: Headers 
     */
	public function get_headers(){
			$headers=array();
		foreach (getallheaders() as $name => $value) {
			$headers[$name] = $value;
		}
		return $headers;
	}
	

}
?>
