<?php 

class ControllerCheckoutDostavimajax extends Controller { 
	private $error = array();
//здесь также можно сделать запрос к базе и получить номер заказа	
	public function index() {
        
        $this->load->language('checkout/cart');
        
        $data['products'] = array();
        
        $products = $this->cart->getProducts();
		
		
$json = array();
if(isset($this->request->post['name'])){

$json['success'] = $this->cart->getProducts();

}
$this->response->setOutput(json_encode($json));

        
}
}
?>