<?php 
class ModelSettingSettingdostavim extends Model {
	public function getSettingDostavim($group, $key, $store_id = 0) {
		$data = array(); 
		
		$query = $this->db->query("SELECT setting_id FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `code` = 'dostavimchekaut' AND `key` = '".$key."'");
		
		foreach ($query->rows as $result) {
				$data = $result;

		}
		
		return $data;
	}
	
	public function getPriceDostavim($key) {
		$data2 = array(); 
		
		$query2 = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting  WHERE `key` = '".$key."'");
		
		foreach ($query2->rows as $result2) {
				$data2 = $result2;

		}
		
		return $data2;
	}
	
	public function setSettingDostavim($valued_dostav,$id_setting) {
		$this->db->query("UPDATE " . DB_PREFIX . "setting  SET value= '" . $valued_dostav . "' WHERE setting_id = '" . (int)$id_setting."'");
	}	
	
	public function setCityDostavim($valued_city_dostav,$id_setting) {
		$this->db->query("UPDATE " . DB_PREFIX . "setting  SET value= '" . $valued_city_dostav . "' WHERE setting_id = '" . (int)$id_setting."'");
	}	
	public function setFlagDostavim($valued_flag_dostav,$id_setting) {
		$this->db->query("UPDATE " . DB_PREFIX . "setting  SET value= '" . $valued_flag_dostav . "' WHERE setting_id = '" . (int)$id_setting."'");
	}
	
	public function updateOrderDostavim($valued_order,$id_sorder) {
		$this->db->query("UPDATE " . DB_PREFIX . "order  SET accept_language= '" . $valued_order . "' WHERE order_id = '" . (int)$id_sorder."'");
	}		
	
	
}
?>