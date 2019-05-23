<?php
class ControllerExtensionTotalDostavimPay extends Controller
{

	public $direct = false;
	public $extension = "dostavim_pay";  //название модуля
	public $options = array(
		'geo_zone' => 'select',
		'status' => 'select',
		'sort_order' => 'input');
	public $error = array();
	
	public function index()
	{

		//$this->db->query("UPDATE " . DB_PREFIX . "setting SET `key` = '" . "total_".$this->extension."_status" . "'  WHERE `key` = '" . $this->extension."_status" . "'");

	
		$this->document->setTitle('Управление оплатой от Dostav.im');
		
        $data['module_id'] = str_replace('_', '-', $this->extension);		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');		
		
		$data['extension'] = $this->extension;
		$data['token'] = $this->session->data['user_token']; 

		
		//var_dump(${'data'});
		$this->load->model('setting/setting');
		
		$this->load->model('setting/dostavsetting');


		
		$request_get_post = $this->request->server['REQUEST_METHOD'];
				
		$this->language->load('extension/total'.'/'.$this->extension);	
		
		if ((strpos($this->request->get['route'], 'uninstall') !== false) || (strpos($this->request->get['route'], 'install') !== false)) return; 
		
		//if (file_exists(DIR_APPLICATION.'model/extension/'.'total'.'/'.$this->extension.'.php')) {  
		//var_dump('66666');
		//	$this->load->model('extension/total'.'/'.$this->extension);
		//	
		//}

		
		$data['name_module_dostavim'] = 'Управление оплатой от Dostav.im';
		
		//$this->load->model('setting/setting');
		//
		//$this->load->model('setting/dostavsetting');
		//
		//$status_true = $this->model_setting_dostavsetting->getSettingValue('total_'.$this->extension.'_status');
		//if($status_true == false)		
		//$this->model_setting_dostavsetting->renameSetting($this->extension.'_status');
        //
		//$sort_order_true = $this->model_setting_dostavsetting->getSettingValue('total_'.$this->extension.'sort_order');
		//if($sort_order_true == false)				
		//$this->model_setting_dostavsetting->renameSetting($this->extension.'sort_order');


		if ($this->direct) {
			$this->load->model('setting/module');
			$id = isset($this->request->get['module_id']) ? $this->request->get['module_id'] : 0;
		}
		
		if (!empty($id) && ($request_get_post != 'POST')) {
			$info = $this->model_setting_module->getModule($id);
		}
								
		if (($request_get_post == 'POST') && $this->validate()) {
			if (method_exists($this, 'accep_dostav')) { 			
				$this->accep_dostav($this->request->post);
			}

			if ($this->direct) {
				$this->request->post['name'] = $this->request->post[$this->extension.'_name']; 
				$this->request->post['total_status'] = $this->request->post[$this->extension.'_status'];

				$data['total_'.$this->extension.'_status'] = 1;
				$data['total_'.$this->extension.'sort_order'] = 1;
				//var_dump($this->request->post[$this->extension.'_name']);			


				
				if (!empty($id)) {
					$this->model_setting_module->editModule($id, $this->request->post);
				} else {
					$this->model_setting_module->addModule($this->extension, $this->request->post);
					
					$query = $this->db->query("SELECT MAX(module_id) AS id FROM `".DB_PREFIX."module` WHERE code = '".$this->extension."'"); 
					$id = $query->row['id'];  
				}
			} else {
				$this->model_setting_setting->editSetting($this->extension, $this->request->post);
			}
						
			if (empty($this->session->data['success'])) {
				$this->session->data['success'] = 'Успешно';
			}
		}
		
		if (isset($this->session->data['success'])) $data['success'] = $this->session->data['success'];
		else $data['success'] = "";
		
		$this->session->data['success'] = "";
			
		$data['breadcrumbs'] = array();		
		$data['html_count'] = 0;
		
		$data['breadcrumbs'][] = array(
			'text' => 'Главная',
			'href' => $this->url->link('common/dashboard', 'user_token='.$data['token'], true));
		
			$data['breadcrumbs'][] = array(
				'text' => 'учитывать в заказе',
				'href' => $this->url->link('marketplace/extension', 'user_token='.$data['token'], true));

			$data['breadcrumbs'][] = array(
				'text' => $data['name_module_dostavim'],
				'href' => $this->url->link('extension/total'.'/'.$this->extension, 'user_token='.$data['token'].(!empty($id) ? '&module_id='.$id : ''), true));
			
			$data['action'] = $this->url->link('extension/total'.'/'.$this->extension, 'user_token='.$data['token'].(!empty($id) ? '&module_id='.$id : ''), 'SSL');
			$data['exit'] = $this->url->link('extension/'.'total', 'user_token='.$data['token'], 'SSL');
		

		$this->load->model('localisation/geo_zone');
		$geo_zones = $this->model_localisation_geo_zone->getGeoZones();
		
		$data['geo_zone'][] = array(0, 'все геозоны');
		
		foreach ($geo_zones as $geo_zone) {
			$data['geo_zone'][] = array($geo_zone['geo_zone_id'], $geo_zone['name']);
		}
		
		$this->load->model('localisation/order_status');
        $statuses = $this->model_localisation_order_status->getOrderStatuses();
		

        $data['order_status'] = array();

        foreach ($statuses as $status) {
        	$data['order_status'][] = array($status['order_status_id'], $status['name']);
        }
		
		$data['status'] = array(
			array('0', 'отключен'),
			array('1', 'включен'));
		
		$this->load->model('localisation/stock_status');
		$statuses = $this->model_localisation_stock_status->getStockStatuses();
		
		
        foreach ($statuses as $status) {
        	$data['stock_status'][] = array($status['stock_status_id'], $status['name']);
        }

				
		$data['settings'] = array(
			'payments' => array(
				'discounts' => 'html'),		
		
			'main' => array_merge(array(
				'inactive_discounts' => 'checkbox',	
				'add_name' => 'radio',				
				'geo_zone' => 'select'
				), $this->options
				)
);
		
		$this->load->model('setting/extension');
		
	
		$shippings = array('shipping');
		
		$data['totals'] = array();
		$data['inactive_discounts'] = array();
		
		foreach ($shippings as $inactive) {
			
			$text = 'Способ доставки';
			
			$items = $this->model_setting_extension->getInstalled($inactive);

			foreach ($items as $item) {
				$this->language->load('extension/'.$inactive.'/'.$item); 
				$data['inactive_discounts'][] = array($inactive.':'.$item, $text.': '.$this->language->get('heading_title'));
				
				if ($inactive == 'total') $data['totals'][] = array($item, $this->language->get('heading_title'));
			}
		}
		
		$data['payments'] = array();

		$payments = $this->model_setting_extension->getInstalled('payment');
		
		foreach ($payments as $payment) {
			$this->language->load('extension/payment/'.$payment);
			$data['payments'][] = array($payment, $this->language->get('heading_title'));
		}
				
		$this->language->load('extension/total'.'/'.$this->extension);					
		$key_pauments = array('discounts');
		

		
		foreach ($key_pauments as $key_paument) {		
			if (isset($this->request->post[$this->extension.'_'.$key_paument])) {
				$data[$key_paument] = $this->request->post[$this->extension.'_'.$key_paument];
			} elseif ($this->config->get($this->extension.'_'.$key_paument)) {
				$data[$key_paument] = $this->config->get($this->extension.'_'.$key_paument);
			} else {
				$data[$key_paument] = array(0 => array('payment' => '', 'minimum' => '', 'maximum' => '', 'value' => ''));
			}

			if ($data['payments']) {
				$html = "<table id='".$key_paument."' class='items table table-striped table-bordered table-hover' style='width: 100%;'>";
			$html .= "<tr class='item-row'><td style='font-size: 18px;'>Способ оплаты</td><td style='font-size: 18px;'>Мин. порог стоимости</td><td style='font-size: 18px;'>Макс. порог стоимости</td><td style='font-size: 18px;'>значение скидки (%,число)</td><td></td></tr>";
				foreach ($data[$key_paument] as $itemkey => $itemdata) {
					$html .= "<tr id='".$key_paument."-".$itemkey."' class='item-row ".$key_paument."'>";
					$html .= "<td>";
					$html .= "<select name='".$this->extension."_".$key_paument."[".$itemkey."][payment]' class='form-control'>";
					$html .= "<option value=''>-- Выберите способ --</option>";
			
					foreach ($data['payments'] as $payment) {
						$html .= "<option value='".$payment[0]."'".($itemdata['payment'] == $payment[0] ? " selected" : "").">".$payment[1]."</option>";
					}
			
					$html .= "</select>";
					$html .= '</td>';
				
					$html .= "<td>";
					$html .= "<input type='text' name='".$this->extension."_".$key_paument."[".$itemkey."][minimum]' class='form-control' value='".$itemdata['minimum']."' placeholder='Минимум' />";
					$html .= '</td>';
					
					$html .= "<td>";
					$html .= "<input type='text' name='".$this->extension."_".$key_paument."[".$itemkey."][maximum]' class='form-control' value='".$itemdata['maximum']."' placeholder='Максимум' />";
					$html .= '</td>';
					
					$html .= "<td>";
					$html .= "<input type='text' name='".$this->extension."_".$key_paument."[".$itemkey."][value]' class='form-control' value='".$itemdata['value']."' placeholder='Значение' />";
					$html .= '</td>';
              		
              		$html .= "<td class='item-buttons'>";
              	
              		if ($itemkey == (count($data[$key_paument]) - 1)) {
						$html .= "<a data-toggle='tooltip' title='Добавить' class='btn btn-primary add-item'>Добавить</a>";
   	    	   		} else {
   	    	    		$html .= "<a data-toggle='tooltip' title='Удалить' class='btn btn-danger remove-item'>Удалить</a>";
   	    	   		}
   	    	   												
              		$html .= "</td>";
              		$html .= "</tr>";
				}
			
				$html .= '</table>';
    		} else {
    			$html = 'Нет способов оплаты';
    		}
    		
    		$data[$this->extension.'_'.$key_paument] = $html;
    	}
		
		$data['entry_add_name'] = 'отображать заголовок';		
		$data['entry_inactive_discounts'] = 'применить к доставке';		
		$data['entry_discounts'] = 'зависимости оплаты';
		$data['entry_status'] = 'статус';		
		$data['entry_sort_order'] = 'порядок сортировки';		
		$data['entry_geo_zone'] = 'геозона';
		
		//var_dump(!empty($id));
		
		if (!empty($id) && ($request_get_post != 'POST')) {
			$info = $this->model_setting_module->getModule($id);
		}
		
		foreach ($data['settings'] as $html => $options) {
			
			foreach ($options as $key => $type) {			
				$from_post = (isset($this->request->post[$this->extension.'_'.$key]) ? $this->request->post[$this->extension.'_'.$key] : "");
				
				//print_r($info);	
				//print_r($info[$this->extension.'_'.$key]);					
				
				$from_config = (!empty($info) && isset($info[$this->extension.'_'.$key]) ? $info[$this->extension.'_'.$key] : $this->config->get($this->extension.'_'.$key));				
				
				
				$default = ($type == 'checkbox' ? array() : "");
			
				if (!isset($data[$this->extension.'_'.$key])) {
					
		//print_r("<pre>");	
		//print_r($from_post);	
		//print_r($from_config);	
		//print_r("</pre>");						
										
					if (!empty($from_post)) $data[$this->extension.'_'.$key] = $from_post;
					elseif (isset($from_config)) $data[$this->extension.'_'.$key] = $from_config;
					else $data[$this->extension.'_'.$key] = $default;
				}
			}
		}
		

		if (isset($this->request->post['total_dostavim_pay_status'])) {
			$data['total_dostavim_pay_status'] = $this->request->post['total_dostavim_pay_status'];
		} else {
			$data['total_dostavim_pay_status'] = $this->config->get('total_dostavim_pay_status');
		}

		if (isset($this->request->post['total_dostavim_pay_sort_order'])) {
			$data['total_dostavim_pay_sort_order'] = $this->request->post['total_dostavim_pay_sort_order'];
		} else {
			$data['total_dostavim_pay_sort_order'] = $this->config->get('total_dostavim_pay_sort_order');
		}	
		
		//var_dump($data);		
		
		if (method_exists($this, 'setDefaults')) {
			$this->setDefaults($data);
		}
					
		if (isset($this->session->data['errors'])) {
			foreach ($this->session->data['errors'] as $key => $text) {
				$this->error[$key] = $text;
			}
			
			unset($this->session->data['errors']);
		}
		
		if (!empty($this->error)) {
			$data['errors'] = $this->error;
		} else {
			$data['errors'] = '';
		}



				$status_true = $this->model_setting_dostavsetting->getSettingValue('total_'.$this->extension.'_status');
				
				var_dump($status_true);
				
				if($status_true == 'notfaund')		
				$this->model_setting_dostavsetting->renameSetting($this->extension.'_status');
		        
				$sort_order_true = $this->model_setting_dostavsetting->getSettingValue('total_'.$this->extension.'_sort_order');
				if($sort_order_true == 'notfaund')						
				$this->model_setting_dostavsetting->renameSetting($this->extension.'_sort_order');	
//var_dump($data);				


		$this->response->setOutput($this->load->view('extension/total'."/".$this->extension, $data));
	}
	
	private function accep_dostav(&$post_request)
	{
		$post_request[$this->extension.'_discounts'] = array_values($post_request[$this->extension.'_discounts']);
		
		return $post_request;
	}
		
	private function validate()
	{
		if (!$this->user->hasPermission('modify', 'extension/total'.'/'.$this->extension)) {
			$this->error['warning'] = 'Внимание: У вас нет прав редактировать'; 
			$this->language->get('heading_title');
		} else {	
			$discounts = array('discounts');
		
			foreach ($discounts as $discount) {
				foreach ($this->request->post[$this->extension.'_'.$discount] as $key => $itemdata) {
					if (strpos($itemdata['value'], "%")) $value = str_replace("%", "", $itemdata['value']);
					else $value = $itemdata['value'];
			
	        		if (!empty($value) && !is_numeric($value)) {
    	    			$this->language->load('extension/payment/'.$itemdata['payment']);
        	   			$this->error[] = sprintf('поле должно быть число', '', $this->language->get('heading_title'));
        	   		}
           		}
	        }

			$this->language->load('extension/total'.'/'.$this->extension);
			$sort_order = array('sort_order');
			$percent = array();
			$date = array();
			$empty = array();
			
			$fields = array_unique(array_merge($sort_order, array_merge($percent, $empty)));
			$post_request = $this->request->post;
			
			if ($fields) {
				foreach ($fields as $field) {
					if (isset($post_request[$this->extension.'_'.$field])) {
						$value = $post_request[$this->extension.'_'.$field];
						
						if (in_array($field, $empty) && !$value) {
							$this->error[] = 'поле неможет быть пустым';
						} elseif (!is_array($value)) {
							$value = trim($value, "%");
							
							if (!empty($value) && !is_numeric($value)) {
								if (in_array($field, $sort_order)) {
									$this->error[] = 'применимо только число';
								} elseif (in_array($field, $percent)) {
									$this->error[] = 'применимо только число или процент';
								}
							} elseif ($value < 0) {
								$this->error[] = 'число 0 или больше';
							}
						}
					} elseif (in_array($field, $empty)) {
						$this->error[] = 'поле неможет быть пустым';
					}
				}
			}
		}
		
		if (!$this->error) return true;
		else return false;
	}
}

?>