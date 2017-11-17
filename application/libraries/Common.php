<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Common {
    private $CI;

    function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->database();
        //~ $this->email_from = $this->CI->config->item('EMAILFROM');
        //~ $this->email_name = $this->CI->config->item('EMAILNAME');
        $this->email_from = 'soniaarora@zapbuild.com';
        $this->email_name = 'Workchew';
    }
    /*
     * Parameters:	$to (mixed) – Comma-delimited string or an array of e-mail addresses
     * Parameters:	$from (string) – “From” e-mail address
     * Parameters:	 $subject (string) – E-mail subject line
     * Parameters:	 $template – E-mail template
     * Parameters:	 $layout (string) – E-mail layout
     * Parameters:	 $data_vars (string) – Array of variables used in message body message 

     */
    public function send_mail($to,$subject, $template , $data_vars = array(),  $layout = 'default'){
		$message = $this->CI->load->view('emails/html/'.$template,$data_vars, TRUE);
        $data['message'] = $this->CI->load->view('layouts/emails/html/'.$layout, array('message' => $message), TRUE);
        
        $config = Array(
			'protocol' => 'smtp',
			'smtp_host' => 'ssl://smtp.googlemail.com',
			'smtp_port' => 465,
			'smtp_user' => 'tech@zapbuild.com',
			'smtp_pass' => 'ztech@15',
			'mailtype'  => 'html', 
			'wordwrap'  => TRUE, 
			'charset'   => 'iso-8859-1'
			);
		$this->CI->load->library('email');
		$this->CI->email->set_newline("\r\n");     
        $this->CI->email->initialize($config);
        $this->CI->email->from('info@workchew.com','Workchew');
        $this->CI->email->to($to);
        $this->CI->email->subject($subject);
        $this->CI->email->message($data['message']);
        return $this->CI->email->send();
		
	}


}

?>
