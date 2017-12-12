<?php
class Cron extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->lang->load('english_lang', 'english');
        $this->load->model('user_model');
        $this->load->library('apn');
    } 
	  
  function send_notifications($deviceToken,$end){
	$this->apn->payloadMethod = 'enhance'; // you can turn on this method for debuggin purpose
	$this->apn->connectToPush();
	// adding custom variables to the notification
	$this->apn->setData(array( 'someKey' => true ));
	$send_result = $this->apn->sendMessage($deviceToken, 'Your suscrption has beed expired on'.$end.' (TIME:'.date('H:i:s').')', /*badge*/ 2, /*sound*/ 'default'  );	
	if($send_result){
		log_message('debug','Sending successful'); 
	}else{	
		log_message('error',$this->apn->error);
	}	
	$this->apn->disconnectPush();
}

  /**
     * Get static pages details
     * URL : http://localhost/workchew/api/user/notification_cron_job
     * METHOD: GET
     * PARAMS: slug (privacy-policy,terms-conditions)
     * RETURN: Json response 
     */

public function notification_cron_job(){
		$get_users_subscription = $this->user_model->getallusers_subscription();
	if(!empty($get_users_subscription)){
		foreach($get_users_subscription as $users){
			$end_date = $users['end_date'];
			$current_date = date('Y-m-d H:i:s');
			if($end_date < $current_date){
				$this->send_notifications($users['device_token'],$users['end_date']);
		 }
		}
	}
}

}
?>
