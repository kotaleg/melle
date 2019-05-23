<?php
    class DostavimPayTotal extends Model
    {
    	public function getTotal($data)
    	{		    		
    		$this->calculateTotal($data);
    	}   
    }


class ModelExtensionTotalDostavimPay extends DostavimPayTotal
{

	public $extension = "dostavim_pay";

	public function percent($percent, $number) {
	
		$number_percent = $number / 100 * $percent;
		
		return $number + $number_percent;
		
	}

	
		
	public function calculateTotal($data)
	{
		

				
		$total_data =& $data['totals'];
		$total =& $data['total'];
		$taxes =& $data['taxes'];
		
		
		if(isset($this->session->data['shipping_method']['cost']))
		$shipping_cost = $this->session->data['shipping_method']['cost'];
		
				
		if (!isset($this->session->data['payment_method'])) return;
		
		$this->language->load('extension/total'.'/'.$this->extension);
		 	
		$group = $this->customer->getGroupId();
		$customer_groups = $this->config->get($this->extension.'_customer_groups');
			
		if (!empty($customer_groups) && !in_array($group, $customer_groups)) return;
			
		$subtotal = $this->cart->getSubTotal();
			
		if (!$subtotal) return;
			
 		if (!$total) $total = $subtotal;

		$valuetypes = array('discounts');
		
		foreach ($valuetypes as $valuetype) {
			$items = $this->config->get($this->extension.'_'.$valuetype);
		
			foreach ($items as $item) {
																		
				if ($item['payment'] == $this->session->data['payment_method']['code']) {
					//var_dump($item['value']);	
					//var_dump($item['minimum']);	
					//var_dump($item['maximum']);	
					if ($item['value'] && (!$item['minimum'] || ($subtotal > $item['minimum'])) && (!$item['maximum'] || ($subtotal < $item['maximum']))) {
						
						$basevalue = $item['value'];
						break 2;
					}
					else
						$basevalue = $item['value'];
				}
			}
		}
		
		if (!empty($basevalue)) {
			if (strpos($basevalue, '%') !== false) {
				$value = $total * (trim($basevalue, '%'))/100;
			}
			else
				$value = $basevalue;

				$value = ($valuetype == 'discounts' ? -abs($value) : abs($value));
		}
		
//добавить функционал
				foreach ($this->config->get($this->extension.'_inactive_'.$valuetype) as $pereschet) {
					$pereschet_values = explode(':', $pereschet);
					if ($pereschet_values[0] == 'shipping')
					$value = 0;
				}
//добавить функционал


if ($this->config->get($this->extension.'_inactive_'.$valuetype)) {

				foreach ($this->config->get($this->extension.'_inactive_'.$valuetype) as $inactive) {
					$inactive_values = explode(':', $inactive);
					
					
					if (($inactive_values[0] == 'shipping') && !empty($this->session->data['shipping_method'])) {
						
						
						
						
						$shipping_method = explode('.', $this->session->data['shipping_method']['code']);
						
						
								
						
						foreach ($inactive_values as $key => $item) {
							
							if ($key==1){
								if ($item == $shipping_method[0]) {
									
									
									if (!empty($basevalue)) {
										
											
										if (strpos($basevalue, '%') !== false){
											$dostavim_value = $shipping_cost * (trim($basevalue, '%'))/100;
																				
										}
										else{
											$dostavim_value = $shipping_cost - $basevalue;
										}
									
										$value = $dostavim_value;
									}
								}
							}
						}
					}

					
				}	
}

								

								
			$title = 'Скидка';//$this->language->get('text_payment_'.$valuetype);
					
			$add_name = $this->config->get($this->extension.'_add_name');
		
			if ($add_name) {
				$info = array();
						
				if ($add_name) $info[] = $this->session->data['payment_method']['title'];
				
				if ($info) $title .= " за способ оплаты: ".implode(", ", $info)."";
			}
			
			if ($this->config->get($this->extension.'_tax_class')) {
				$tax_rates = $this->tax->getRates($value, $this->config->get($this->extension.'_tax_class'));
	
				foreach ($tax_rates as $tax_rate) {
					if (!isset($taxes[$tax_rate['tax_rate_id']])) $taxes[$tax_rate['tax_rate_id']] = $tax_rate['amount'];
					else $taxes[$tax_rate['tax_rate_id']] += $tax_rate['amount'];
				}
			}

			$total += $value;
					
			
			$total_data[] = array(
				'code' => $this->extension,
		     	'title' => $title,
    		    'text' => ($value < 0 ? "-" : "").$this->currency->format(abs($value), $this->config->get('config_currency')),
        		'value' => $value,
				'sort_order' => $this->config->get($this->extension.'_sort_order'));

	}	
}

?>