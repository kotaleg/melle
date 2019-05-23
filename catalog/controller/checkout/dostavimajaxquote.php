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
		if(isset($setting_info_id['setting_id']))
		$param1 =  $setting_info_id['setting_id'];		
		$json = array();
		
		if((isset($this->request->post['name']))and($this->request->post['name']=='dostavim')){		
		$json['success'] = empty($setting_info_id);//$param1;
		//оригинальный opencart
		$this->load->model('setting/settingdostavim');
		$param1 =  $this->model_setting_settingdostavim->getPriceDostavim('shipping_dostavimchekaut_token');
	    $tokenDostavim = $param1["value"];
		
		$this->response->setOutput(json_encode($tokenDostavim));	 //16.04.2019	
		//оригинальный opencart
		}
		
		if((isset($this->request->post['name']))and($this->request->post['name']=='dostavimCash')){	
			$innerJs1 = '<hr class="hidepvz"><div style="margin-right:10px;margin-left: 30px;color:#999">';
			
			//if (!empty($this->request->post['dostadress'])){
				$innerJs2 = '<span>Адрес: </span> <span id="dostadress">'.$this->request->post['dostadress'].'</span></br><hr class="hidepvz">';
			//}
			//else
			//	$innerJs2 = '';

			//if (!empty($this->request->post['dostmysd'])){			
			$innerJs3 = '<span>Служба доставки: </span> <span id="dostmysd"><img src="https://dostav.im/img/'.$this->request->post['dostmysd'].'.png" style="width: 40px; margin-right: 10px;top: 8px;"><!--'.$this->request->post['dostmysd'].'--></span></br><hr class="hidepvz">';
			//}
			//else
			//	$innerJs3 = '';			

			if (!empty($this->request->post['dostphone'])){				
				$innerJs4 = '<span>Телефон:</span> <span id="dostphone">'.$this->request->post['dostphone'].'</span></br><hr class="hidepvz">';
			}
			else      
				$innerJs4 = '<span>Телефон:</span> <span id="dostphone">Нет данных по телефонам</span></br><hr class="hidepvz">';

			if (!empty($this->request->post['dosttime'])){				
				$innerJs5 = '<span>Режим работы:</span> <span id="dosttime">'.$this->request->post['dosttime'].'</span></br>';	
			}
			else
				$innerJs5 = '<span>Режим работы:</span> <span id="dosttime">Нет данных по режиму</span></br>';

			//16.04.2019
			if (!empty($this->request->post['dostday_max'])){				
				$innerJs10 = '<span style="display:none">Макс дн:</span> <span style="display:none" id="dostday_max">'.$this->request->post['dostday_max'].'</span><hr class="hidepvz" style="display:none">';
			}
			else      
				$innerJs10 = '<span style="display:none">Макс дн:</span> <span style="display:none" id="dostday_max">Нет данных по макс.  дн</span><hr class="hidepvz" style="display:none">';
			
			if (!empty($this->request->post['dostday_min'])){				
				$innerJs11 = '<span style="display:none">Мин дн:</span> <span style="display:none" id="dostday_min">'.$this->request->post['dostday_min'].'</span><hr class="hidepvz" style="display:none">';
			}
			else      
				$innerJs11 = '<span style="display:none">Мин дн:</span> <span id="dostday_min" style="display:none">Нет данных по мин.  дн</span><hr class="hidepvz" style="display:none">';
			
			if (!empty($this->request->post['dostprice'])){				
				$innerJs12 = '<span style="display:none">Цена:</span> <span style="display:none" id="dostprice">'.$this->request->post['dostprice'].'</span><hr class="hidepvz" style="display:none">';
			}
			else      
				$innerJs12 = '<span style="display:none">Цена:</span> <span style="display:none" id="dostprice">Нет данных по цене</span><hr class="hidepvz" style="display:none">';
			//16.04.2019
			
			$innerJs6 = '</div><hr class="hidepvz">';
	
			
			$innerJs8 = $innerJs1.$innerJs2.$innerJs3.$innerJs4.$innerJs5.$innerJs10.$innerJs11.$innerJs12.$innerJs6;
			//$this->cache->delete('dost.shipping.pvzgenerate');		 
			//file_put_contents(DIR_CACHE . 'cache.dost.shipping.pvzgenerate.' . (time() + (3600 * 24 * 7)), serialize($innerJs8));//запись в кешь и 
			//$number_session = $this->customer->request->cookie["PHPSESSID"]; // если куки неподключены выкидовать ошибку
			$number_session = $this->customer->request->cookie["OCSESSID"]; // если куки неподключены выкидовать ошибку
			$time_replace = (time() + (3600 * 24 * 7));	
			//16.04.2019
			$this->cache->delete('dost.day3.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.day3.'.$number_session.'.'.$time_replace, $this->request->post['dostday_max']); //запись в кешь яндекс карту				
			$this->cache->delete('dost.day_min3.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.day_min3.'.$number_session.'.'.$time_replace, $this->request->post['dostday_min']); //запись в кешь яндекс карту	
			
			$this->cache->delete('dost.price3.'.$number_session);		//удалить кешь генерации яндекс карт	 
			$this->cache->set('dost.price3.'.$number_session.'.'.$time_replace, $this->request->post['dostprice']); //запись в кешь яндекс карту	
			
			$this->cache->delete('dost.pvzgenerate.'.$number_session);	
			$this->cache->set('dost.pvzgenerate.'.$number_session.'.'.$time_replace, $innerJs8);
			$this->response->setOutput(json_encode($this->request->post));
		}		
		
	
		//$this->response->setOutput(json_encode($tokenDostavim)); 	 //16.04.2019

        
		}
}
?>