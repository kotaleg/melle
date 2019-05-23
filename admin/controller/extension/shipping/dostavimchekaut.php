<?php
class ControllerExtensionShippingDostavimchekaut extends Controller {
	private $error = array(); 

	public function index() {   
		$this->load->language('extension/shipping/dostavimchekaut');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_dostavimchekaut', $this->request->post);		

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_none'] = $this->language->get('text_none');
		$data['text_dostavimchekaut_sort_order'] = $this->language->get('text_dostavimchekaut_sort_order');
		$data['text_dostavimchekaut_name'] = $this->language->get('text_dostavimchekaut_name');
		
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_cost'] = $this->language->get('entry_cost');
		$data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['button_add_module'] = $this->language->get('button_add_module');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_remove'] = $this->language->get('button_remove');
		$data['token_dost'] = $this->session->data['user_token'];


		//opencart 3
				$search_array_module[1] = array("name" => "Самовывоз от продавца", "cost" => "0", "tax_class_id" => "0", "geo_zone_id" => "0", "status" => "0", "sort_order" => "1");
				$search_array_module[2] = array("name" => "Своим курьером", "cost" => "0", "tax_class_id" => "0", "geo_zone_id" => "0", "status" => "0", "sort_order" => "1");
				$search_array_module[3] = array("name" => "Пункты самовывоза", "cost" => "0", "tax_class_id" => "0", "geo_zone_id" => "0", "status" => "1", "sort_order" => "1");
				$search_array_module[4] = array("name" => "Курьерская служба", "cost" => "0", "tax_class_id" => "0", "geo_zone_id" => "0", "status" => "1", "sort_order" => "1");	
				$search_array_module[5] = array("name" => "Почта России", "cost" => "0", "tax_class_id" => "0", "geo_zone_id" => "0", "status" => "1", "sort_order" => "1");	

		
		$data['search_array_module'] = $search_array_module;
		//opencart 3
		
	
		
		

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=shipping', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/dostavimchekaut', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/shipping/dostavimchekaut', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);
		
		$data['modules'] = array();

		if (isset($this->request->post['shipping_dostavimchekaut'])) {
			$data['modules'] = $this->request->post['shipping_dostavimchekaut'];
		} elseif ($this->config->get('shipping_dostavimchekaut')) {
			$data['modules'] = $this->config->get('shipping_dostavimchekaut');
		}

		if (isset($this->request->post['shipping_dostavimchekaut_cost'])) {
			$data['shipping_dostavimchekaut_cost'] = $this->request->post['shipping_dostavimchekaut_cost'];
		} else {
			$data['shipping_dostavimchekaut_cost'] = $this->config->get('shipping_dostavimchekaut_cost');
		}

		if (isset($this->request->post['shipping_dostavimchekaut_tax_class_id'])) {
			$data['shipping_dostavimchekaut_tax_class_id'] = $this->request->post['shipping_dostavimchekaut_tax_class_id'];
		} else {
			$data['shipping_dostavimchekaut_tax_class_id'] = $this->config->get('shipping_dostavimchekaut_tax_class_id');
		}

		$this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		if (isset($this->request->post['shipping_dostavimchekaut_geo_zone_id'])) {
			$data['shipping_dostavimchekaut_geo_zone_id'] = $this->request->post['shipping_dostavimchekaut_geo_zone_id'];
		} else {
			$data['shipping_dostavimchekaut_geo_zone_id'] = $this->config->get('shipping_dostavimchekaut_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['shipping_dostavimchekaut_status'])) {
			$data['shipping_dostavimchekaut_status'] = $this->request->post['shipping_dostavimchekaut_status'];
		} else {
			$data['shipping_dostavimchekaut_status'] = $this->config->get('shipping_dostavimchekaut_status');
		}

		if (isset($this->request->post['shipping_dostavimchekaut_sort_order'])) {
			$data['shipping_dostavimchekaut_sort_order'] = $this->request->post['shipping_dostavimchekaut_sort_order'];
		} else {
			$data['shipping_dostavimchekaut_sort_order'] = $this->config->get('shipping_dostavimchekaut_sort_order');
		}				

		//$data['dostavimchekaut_name1'] = "";
		//if (isset($this->request->post['dostavimchekaut_name1'])) {
		//	$data['dostavimchekaut_name1'] = $this->request->post['dostavimchekaut_name1'];
		//} elseif ($this->config->get('dostavimchekaut_name1')) {
		//	$data['dostavimchekaut_name1'] = $this->config->get('dostavimchekaut_name1');
		//}
        //
		//$data['dostavimchekaut_name2'] = "";
		//if (isset($this->request->post['dostavimchekaut_name2'])) {
		//	$data['dostavimchekaut_name2'] = $this->request->post['dostavimchekaut_name2'];
		//} elseif ($this->config->get('dostavimchekaut_name2')) {
		//	$data['dostavimchekaut_name2'] = $this->config->get('dostavimchekaut_name2');
		//}
        //
		//$data['dostavimchekaut_name3'] = "";
		//if (isset($this->request->post['dostavimchekaut_name3'])) {
		//	$data['dostavimchekaut_name3'] = $this->request->post['dostavimchekaut_name3'];
		//} elseif ($this->config->get('dostavimchekaut_name3')) {
		//	$data['dostavimchekaut_name3'] = $this->config->get('dostavimchekaut_name3');
		//}
        //
		//$data['dostavimchekaut_name4'] = "";
		//if (isset($this->request->post['dostavimchekaut_name4'])) {
		//	$data['dostavimchekaut_name4'] = $this->request->post['dostavimchekaut_name4'];
		//} elseif ($this->config->get('dostavimchekaut_name4')) {
		//	$data['dostavimchekaut_name4'] = $this->config->get('dostavimchekaut_name4');
		//}

//==============================================================================================================
		$data['shipping_dostavimchekaut_rusha_markup'] = "";
		if (isset($this->request->post['shipping_dostavimchekaut_rusha_markup'])) {
			$data['shipping_dostavimchekaut_rusha_markup'] = $this->request->post['shipping_dostavimchekaut_rusha_markup'];
		} elseif ($this->config->get('shipping_dostavimchekaut_rusha_markup')) {
			$data['shipping_dostavimchekaut_rusha_markup'] = $this->config->get('shipping_dostavimchekaut_rusha_markup');
		}
		$data['shipping_dostavimchekaut_moscow_markup'] = "";
		if (isset($this->request->post['shipping_dostavimchekaut_moscow_markup'])) {
			$data['shipping_dostavimchekaut_moscow_markup'] = $this->request->post['shipping_dostavimchekaut_moscow_markup'];
		} elseif ($this->config->get('shipping_dostavimchekaut_moscow_markup')) {
			$data['shipping_dostavimchekaut_moscow_markup'] = $this->config->get('shipping_dostavimchekaut_moscow_markup');
		}
		$data['shipping_dostavimchekaut_piter_markup'] = "";
		if (isset($this->request->post['shipping_dostavimchekaut_piter_markup'])) {
			$data['shipping_dostavimchekaut_piter_markup'] = $this->request->post['shipping_dostavimchekaut_piter_markup'];
		} elseif ($this->config->get('shipping_dostavimchekaut_piter_markup')) {
			$data['shipping_dostavimchekaut_piter_markup'] = $this->config->get('shipping_dostavimchekaut_piter_markup');
		}
		//16.04.2018
		
		$data['shipping_dostavimchekaut_rusha'] = "";
		if (isset($this->request->post['shipping_dostavimchekaut_rusha'])) {
			$data['shipping_dostavimchekaut_rusha'] = $this->request->post['shipping_dostavimchekaut_rusha'];
		} elseif ($this->config->get('shipping_dostavimchekaut_rusha')) {
			$data['shipping_dostavimchekaut_rusha'] = $this->config->get('shipping_dostavimchekaut_rusha');
		}
				
		$data['shipping_dostavimchekaut_nalojka'] = "";
		if (isset($this->request->post['shipping_dostavimchekaut_nalojka'])) {
			$data['shipping_dostavimchekaut_nalojka'] = $this->request->post['shipping_dostavimchekaut_nalojka'];
		} elseif ($this->config->get('shipping_dostavimchekaut_nalojka')) {
			$data['shipping_dostavimchekaut_nalojka'] = $this->config->get('shipping_dostavimchekaut_nalojka');
		}
		
		$data['shipping_dostavimchekaut_moscow'] = "";
		if (isset($this->request->post['shipping_dostavimchekaut_moscow'])) {
			$data['shipping_dostavimchekaut_moscow'] = $this->request->post['shipping_dostavimchekaut_moscow'];
		} elseif ($this->config->get('shipping_dostavimchekaut_moscow')) {
			$data['shipping_dostavimchekaut_moscow'] = $this->config->get('shipping_dostavimchekaut_moscow');
		}
		//16.04.2018				
		$data['shipping_dostavimchekaut_piter'] = "";
		if (isset($this->request->post['shipping_dostavimchekaut_piter'])) {
			$data['shipping_dostavimchekaut_piter'] = $this->request->post['shipping_dostavimchekaut_piter'];
		} elseif ($this->config->get('shipping_dostavimchekaut_piter')) {
			$data['shipping_dostavimchekaut_piter'] = $this->config->get('shipping_dostavimchekaut_piter');
		}
		//16.04.2018		

		$data['shipping_dostavimchekaut_token'] = "";
		if (isset($this->request->post['shipping_dostavimchekaut_token'])) {
			$data['shipping_dostavimchekaut_token'] = $this->request->post['shipping_dostavimchekaut_token'];
		} elseif ($this->config->get('shipping_dostavimchekaut_token')) {
			$data['shipping_dostavimchekaut_token'] = $this->config->get('shipping_dostavimchekaut_token');
		}
		
//==============================================================================================================		
		
		
		//$this->template = 'shipping/dostavimchekaut.tpl';
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/dostavimchekaut', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/shipping/dostavimchekaut')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
?>