<?php 

class ControllerCheckoutDostavimajaxquote extends Controller { 

	private $error = array();
//здесь также можно сделать запрос к базе и получить номер заказа	
	public function index() {
		//
		//$postData = file_get_contents('php://input');
		//$data = json_decode($postData, true);
		//if(isset($this->request->post['name'])){
		//			$valued_dostav = $this->request->post['quotedostavim'];
		//		}		
		
		
		$this->load->model('setting/settingdostavim');
		$setting_info_id = $this->model_setting_settingdostavim->getSettingDostavim('shipping_dostavimchekaut_token', 0);
		//if (!empty($setting_info_id)) {	
		//$kdkdjkddjd = 1;
		//$this->model_setting_settingdostavim->setSettingDostavim($valued_dostav, $setting_info_id['setting_id']);
		//}
		if(isset($setting_info_id['setting_id']))
		$param1 =  $setting_info_id['setting_id'];

		
$json = array();
if(isset($this->request->post['name'])){

$json['success'] = empty($setting_info_id);//$param1;

//оригинальный opencart
		$this->load->model('setting/settingdostavim');
		$param1 =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_token');
	    $tokenDostavim = $param1["value"];
//оригинальный opencart
}
$this->response->setOutput(json_encode($tokenDostavim));

        
}
}
?>