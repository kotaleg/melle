<?php 

class ControllerSettingDostavimajaxquote extends Controller { 
	private $error = array();
	
//здесь также можно сделать запрос к базе и получить номер заказа	
		public function index() {

		if(isset($this->request->post['name'])){
			$this->load->model('setting/settingdostavim');			
			$orderValue= $this->request->post['orderIddostavim'];
			$orderId = $this->request->post['orderIdOC'];	
            $param1 = $this->model_setting_settingdostavim->updateOrderDostavim($orderValue, $orderId);
		}
		
		if(isset($this->request->post['nametoken'])){
			$this->load->model('setting/settingdostavim');	
			$dostavimchekaut_token =    $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_token');
			$param1 =  $dostavimchekaut_token["value"];
		}
	

		$this->response->setOutput(json_encode($param1));
        
		}
}
?>