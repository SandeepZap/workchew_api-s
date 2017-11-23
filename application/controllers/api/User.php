<?php
require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
use \Firebase\JWT\JWT;
class User extends REST_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('user_model');
    }

    /**
     * User Login API
     * URL : http://localhost/index.php/workchew/api/user/login
     * METHOD: POST
     * PARAMS: Email, password
     * RETURN: Json response 
     */
    public function login_post() {
        $email = $this->post('email');
        $password = md5($this->post('password'));
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');
        if ($this->form_validation->run()) {
			$id = $this->user_model->login($email,$password);
			if($id) {
				$user = $this->user_model->get_row(array('id' => $id), array('first_name', 'last_name' ,'id','email','username'));
				$token['id'] = $id;
				$token['email'] = $email;
				$date = new DateTime();
				$token['iat'] = $date->getTimestamp();
				$token['exp'] = $date->getTimestamp() + 60*60*5;
				$output['id_token'] = JWT::encode($token, "my Secret key!");
                        $output['status']['status'] = 'success';
                        $output['status']['status_code'] = '200';
                        $output['message'] = 'You are Login successfully.';
                        $output['response']['data'] = $user;
                        $this->set_response($output, REST_Controller::HTTP_OK);	
			} else {
						$response['status']['status'] = 'failure';
                        $response['status']['status_code'] = '401';
                        $response['message'] = 'Login fail';	
						$this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);	
			}
		}else{
						$response['status']['status'] = 'failure';
                        $response['status']['status_code'] = '422';
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
							$user = $this->user_model->get_row(array('id' => $result), array('first_name', 'last_name' ,'id','email','username'));
							$response['status']['status'] = 'success';
							$response['status']['status_code'] = '201';
							$response['message'] = 'You are registered successfully.';
							$response['response']['data'] = $user;
							$this->set_response($response, REST_Controller::HTTP_CREATED);	
						}else{
							$response ['status']['status'] = 'failure';
							$response['status']['status_code'] = '500';
							$response['message'] = 'Internal server error';	
							$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);	
						}		
		}else{
							$response ['status']['status'] = 'failure';
							$response['status']['status_code'] = '422';
							$response['message']['data'] = $this->form_validation->error_array();	
							$this->set_response($response, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);	
		}
	}
	
	 /**
     * User Forgot Password API
     * URL : http://localhost/workchew/api/user/forgot_password
     * METHOD: POST
     * PARAMS: Email
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
                                'first_name' => $user->first_name,
                                'email' => $this->post('email'),
                                'password' => $passwordplain
                            );
                            $this->load->library('common');
                            $this->common->send_mail($this->post('email'), 'Workchew: Forgot Password', 'forgot-password-email', $data_vars, 'email');
							$response['status']['status'] = 'success';
							$response['status']['status_code'] = '200';
							$response['message'] = 'Please check your email for your password';
							//$response['pass'] = $passwordplain;
							$this->set_response($response, REST_Controller::HTTP_OK);	
                        } else {
							$response['status']['status'] = 'fail';
							$response['status']['status_code'] = '500';
							$response['message'] = 'Internal server error';
							$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    } else {
							$response['status']['status'] = 'failure';
							$response['status']['status_code'] = '400';
							$response['message'] = 'Email not found';	
							$this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
                    }
                } else {
							$response['status']['status'] = 'failure';
							$response['status']['status_code'] = '422';
							$response['message']['data'] = $this->form_validation->error_array();	
							$this->set_response($response, REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
                }
	}
	
	/**
     * User Login using social API
     * URL : http://localhost/workchew/index.php/api/user/social_login
     * METHOD: POST
     * PARAMS: facebook_token
     * RETURN: Json response 
     */
     
	public function social_login_post(){		
		if($this->post('provider') == 'facebook'){	
//$access_token= "EAAD0hkDSBL0BANGl41ZBf0kKtOZATBKVVWRRuItgYgh5uDmqBX9iZB5fAfIubThuw08fsdb5D8xxYgmY2UdyVfBbnBZCx5BzNHFI14TeS0pLksnaAx0IKapeZBaCLwzU1GvEYLYqyuwDSSZB3eLys3Pkdqs21wnDBweSGoOi5Nm5uYp2U3a5NVPeFLdvZBiYUXqgOTJNcP8LPriCLemRvB0JbwbPXhy8CnLvCuFZCK3LAZBdQDoiqj4qoSag7FnTIC6EZD";
	//		$uid = "164095840998702";
			$access_token = $this->post('access_token');
			$uid = $this->post('uid');
			$url="https://graph.facebook.com/".$uid."/?access_token=".urlencode($access_token);
			$opts = array(
				'http' => array('ignore_errors' => true)
					);
			$context = stream_context_create($opts);
			$json = file_get_contents($url, false, $context);
			$response = json_decode($json, TRUE);
			
				if(isset($response['id'])){
					$get_user = $this->user_model->get_row(array('uid' => $this->post('uid')), array('first_name', 'last_name' ,'id','uid'));
						if(!empty($get_user)){	 
							$token['id'] = $get_user->id;
							$token['uid'] = $get_user->uid;
							$date = new DateTime();
							$token['iat'] = $date->getTimestamp();
							$token['exp'] = $date->getTimestamp() + 60*60*5;
							$output['id_token'] = JWT::encode($token, "my Secret key!");
							$output['status']['status'] = 'success';
							$output['status']['status_code'] = '200';
							$output['message'] = 'You are Login successfully.';
							$output['response']['data'] = $get_user;
							$this->set_response($output, REST_Controller::HTTP_OK);	
					 
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
									$token['id'] = $user->id;
									$token['uid'] = $user->uid;
									$date = new DateTime();
									$token['iat'] = $date->getTimestamp();
									$token['exp'] = $date->getTimestamp() + 60*60*5;
									$output['id_token'] = JWT::encode($token, "my Secret key!");
									$output['status']['status'] = 'success';
									$output['status']['status_code'] = '200';
									$output['message'] = 'You are Login successfully.';
									$output['response']['data'] = $user;
									$this->set_response($output, REST_Controller::HTTP_OK);	
								} else {
									$response['status']['status'] = 'failure';
									$response['status']['status_code'] = '401';
									$response['message'] = 'Login fail';	
									$this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);	
								}
				
						}else{
								$response['status']['status'] = 'failure';
								$response['status_code'] = '500';
								$response['message'] = 'Internal server error';	
								$this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);	
						} 
						}
					}else{
						$response['status']['status'] = 'failure';
                        $response['status']['status_code'] = '401';
                        $response['message'] = 'Unautorized user';	
						$this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
					}		
			} else if($this->post('provider') == 'twitter'){           
                $username =  $this->post('username');
                $url = "https://twitter.com/".$username;
                $contents = @file_get_contents($url);
                if ($contents) {
                    $get_user = $this->user_model->get_row(array('username' => $this->post('username')), array('first_name', 'last_name' ,'id','username'));
                    if(!empty($get_user)){     
                            $token['id'] = $get_user->id;
                            $date = new DateTime();
                            $token['iat'] = $date->getTimestamp();
                            $token['exp'] = $date->getTimestamp() + 60*60*5;
                            $output['id_token'] = JWT::encode($token, "my Secret key!");
                            $output['status']['status'] = 'success';
                            $output['status']['status_code'] = '200';
                            $output['message'] = 'You are Login successfully.';
                            $output['response']['data'] = $get_user;
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
                                        $token['id'] = $user->id;
                                        $token['uid'] = $user->uid;
                                        $date = new DateTime();
                                        $token['iat'] = $date->getTimestamp();
                                        $token['exp'] = $date->getTimestamp() + 60*60*5;
                                        $output['id_token'] = JWT::encode($token, "my Secret key!");
                                        $output['status']['status'] = 'success';
                                        $output['status']['status_code'] = '200';
                                        $output['message'] = 'You are Login successfully.';
                                        $output['response']['data'] = $user;
                                        $this->set_response($output, REST_Controller::HTTP_OK);   
                                    } else {
                                        $response['status']['status'] = 'failure';
                                        $response['status']['status_code'] = '401';
                                        $response['message'] = 'Login fail';   
                                        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);   
                                    }
                   
                            }else{
                                    $response['status']['status'] = 'failure';
                                    $response['status']['status_code'] = '500';
                                    $response['message'] = 'Internal server error';   
                                    $this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);   
                            }
                    }
                } else {
                    $response['status']['status'] = 'failure';
                    $response['status']['status_code'] = '401';
                    $response['message'] = 'Login fail';   
                    $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);           
                }    
            } else if($this->post('provider') == 'linkedin'){
                $url="https://api.linkedin.com/v1/people/~?format=json";
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
                $response = json_decode($json, TRUE);
                if(isset($response['id'])){
                    $get_user = $this->user_model->get_row(array('uid' => $response['id']), array('first_name', 'last_name' ,'id','uid'));
                        if(!empty($get_user)){     
                            $token['id'] = $get_user->id;
                            $token['uid'] = $get_user->uid;
                            $date = new DateTime();
                            $token['iat'] = $date->getTimestamp();
                            $token['exp'] = $date->getTimestamp() + 60*60*5;
                            $output['id_token'] = JWT::encode($token, "my Secret key!");
                            $output['status']['status'] = 'success';
                            $output['status']['status_code'] = '200';
                            $output['message'] = 'You are Login successfully.';
                            $output['response']['data'] = $get_user;
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
                                    $token['id'] = $user->id;
                                    $token['uid'] = $user->uid;
                                    $date = new DateTime();
                                    $token['iat'] = $date->getTimestamp();
                                    $token['exp'] = $date->getTimestamp() + 60*60*5;
                                    $output['id_token'] = JWT::encode($token, "my Secret key!");
                                    $output['status']['status'] = 'success';
                                    $output['status']['status_code'] = '200';
                                    $output['message'] = 'You are Login successfully.';
                                    $output['response']['data'] = $user;
                                    $this->set_response($output, REST_Controller::HTTP_OK);   
                                } else {
                                    $response['status']['status'] = 'failure';
                                    $response['status']['status_code'] = '401';
                                    $response['message'] = 'Login fail';   
                                    $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);   
                                }
               
                        }else{
                                $response['status']['status'] = 'failure';
                                $response['status']['status_code'] = '500';
                                $response['message'] = 'Internal server error';   
                                $this->set_response($response, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);   
                        }
                        }
                    }else{
                        $response['status']['status'] = 'failure';
                        $response['status']['status_code'] = '401';
                        $response['message'] = 'Unautorized user';   
                        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
                    }       
        }    
    
		}
}
?>
