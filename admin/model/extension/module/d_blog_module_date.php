<?php
/*
 *	location: admin/model
 */

class ModelExtensionModuleDBlogModuleDate extends Model {

	public function getStores(){
		$this->load->model('setting/store');
		$stores = $this->model_setting_store->getStores();
		$result = array();
		if($stores){
			$result[] = array(
				'store_id' => 0, 
				'name' => $this->config->get('config_name')
				);
			foreach ($stores as $store) {
				$result[] = array(
					'store_id' => $store['store_id'],
					'name' => $store['name']	
					);
			}	
		}
		return $result;
	}

	/*
	*	Return name of config file.
	*/
	public function getConfigFile($id, $sub_versions){

		$setting = $this->config->get($id.'_setting');

		if(isset($setting['config'])){
			return $setting['config'];
		}

		$full = DIR_SYSTEM . 'config/'. $id . '.php';
		if (file_exists($full)) {
			return $id;
		} 

		foreach ($sub_versions as $lite){
			if (file_exists(DIR_SYSTEM . 'config/'. $id . '_' . $lite . '.php')) {
				return $id . '_' . $lite;
			}
		}
		
		return false;
	}

	/*
	*	Return list of config files that contain the id of the module.
	*/
	public function getConfigFiles($id){
		$files = array();
		$results = glob(DIR_SYSTEM . 'config/'. $id .'*');

		if(!$results) {
            return array();
        }
        
		foreach($results as $result){
			$files[] = str_replace('.php', '', str_replace(DIR_SYSTEM . 'config/', '', $result));
		}
		return $files;
	}

	/*
	*	Get config file values and merge with config database values
	*/
	public function getConfigData($id, $config_key, $store_id, $config_file = false){
		if(!$config_file){
			$config_file = $this->config_file;
		}
		if($config_file){
			$this->config->load($config_file);
		}

		$result = ($this->config->get($config_key)) ? $this->config->get($config_key) : array();

		if(!isset($this->request->post['config'])){
			$this->load->model('setting/setting');
			if (isset($this->request->post[$config_key])) {
				$setting = $this->request->post;
			} elseif ($this->model_setting_setting->getSetting($id, $store_id)) { 
				$setting = $this->model_setting_setting->getSetting($id, $store_id);
			}
			if(isset($setting[$config_key])){
				foreach($setting[$config_key] as $key => $value){
					$result[$key] = $value;
				}
			}
			
		}
		return $result;
	}
}