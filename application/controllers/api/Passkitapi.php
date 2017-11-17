<?php
require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
require_once APPPATH . '/third_party/Passkit.php';
use \Firebase\JWT\JWT;
class Passkitapi extends REST_Controller {
	
    public function __construct() {
        parent::__construct();
        
        
    }
	
	public function passkit_post(){
		$pk = new PassKit('RwyhpmPvaZfABqizppuHYpkWgysbpkit', '3iIFmW5Gu3Vuf44ANC4JgnAHNwacSlRJqATOVEo5W5Luqa9Xb1IgB56fMFm4LVk86APLVZoeqHvkaQY0G85y6CJiBs8SDZMxrPwCzYVclHkhFsz5aNy6sgoUX0aaJWql');
		$data_passbook = array('type' => 'generic','desc' => 'membership template','OrgName' => 'workchew', 'BgColor' => '#FFFFFF' ,'LabelColor' => '#000000', 'FgColor' => '#AAAAAA');
		$data = array('name' => 'WorkchewTemplate','campaignName' => 'MyCampaign','language' => 'en','startDate' => '2017-01-01T00:00:00Z', 'passbook' => $data_passbook);
		$image_files = array('passbook-IconFile' => '/var/www/html/workchew/assets/images/image.png');
		$result = $pk->CreateTemplate($data,$image_files);
		//$result = $pk->getTemplatePasses('test');
		//$this->set_response($result, REST_Controller::HTTP_OK);	
		print_r($result);		
	}
    
    public function capaign_post(){
		$pk = new PassKit('RwyhpmPvaZfABqizppuHYpkWgysbpkit', '3iIFmW5Gu3Vuf44ANC4JgnAHNwacSlRJqATOVEo5W5Luqa9Xb1IgB56fMFm4LVk86APLVZoeqHvkaQY0G85y6CJiBs8SDZMxrPwCzYVclHkhFsz5aNy6sgoUX0aaJWql');
		$data = array('name' => 'MyCampaign','passbookCertId' => 'cvUXsSSk0o2i','startDate' => '2016-01-01T00:00:00Z');
		$result = $pk->CreateCampaign($data);
		print_r($result);
	}
	
	public function generate_post(){
		$pk = new PassKit('RwyhpmPvaZfABqizppuHYpkWgysbpkit', '3iIFmW5Gu3Vuf44ANC4JgnAHNwacSlRJqATOVEo5W5Luqa9Xb1IgB56fMFm4LVk86APLVZoeqHvkaQY0G85y6CJiBs8SDZMxrPwCzYVclHkhFsz5aNy6sgoUX0aaJWql');
		$result = $pk->ListCertificates();
		print_r($result);
	}
	
	public function get_template_get(){
		$pk = new PassKit('RwyhpmPvaZfABqizppuHYpkWgysbpkit', '3iIFmW5Gu3Vuf44ANC4JgnAHNwacSlRJqATOVEo5W5Luqa9Xb1IgB56fMFm4LVk86APLVZoeqHvkaQY0G85y6CJiBs8SDZMxrPwCzYVclHkhFsz5aNy6sgoUX0aaJWql');
		//$result = $pk->GetTemplate('MyTemplate');
		$result = $pk->ListTemplatesByCampaign('MyCampaign');
		//$this->set_response($result, REST_Controller::HTTP_OK);	
		print_r($result);
	}
	
	public function create_pass_post(){ 
			$pk = new PassKit('RwyhpmPvaZfABqizppuHYpkWgysbpkit', '3iIFmW5Gu3Vuf44ANC4JgnAHNwacSlRJqATOVEo5W5Luqa9Xb1IgB56fMFm4LVk86APLVZoeqHvkaQY0G85y6CJiBs8SDZMxrPwCzYVclHkhFsz5aNy6sgoUX0aaJWql');
	$data = array('templateName' => 'MyTemplate');
$result = $pk->CreatePass($data);
print_r($result);
}

public function reterive_pass_get(){
	
	$pk = new PassKit('RwyhpmPvaZfABqizppuHYpkWgysbpkit', '3iIFmW5Gu3Vuf44ANC4JgnAHNwacSlRJqATOVEo5W5Luqa9Xb1IgB56fMFm4LVk86APLVZoeqHvkaQY0G85y6CJiBs8SDZMxrPwCzYVclHkhFsz5aNy6sgoUX0aaJWql');
	$result = $pk->GetPassById('yvjUc8X2AIsvtV');
	print_r($result);
}

public function update_template_put(){
	$pk = new PassKit('RwyhpmPvaZfABqizppuHYpkWgysbpkit', '3iIFmW5Gu3Vuf44ANC4JgnAHNwacSlRJqATOVEo5W5Luqa9Xb1IgB56fMFm4LVk86APLVZoeqHvkaQY0G85y6CJiBs8SDZMxrPwCzYVclHkhFsz5aNy6sgoUX0aaJWql');
	$data = array('startDate' => '2017-11-16T00:00:00Z');
$result = $pk->UpdateTemplateData('MyTemplate',$data);
print_r($result);
}
}
?>
