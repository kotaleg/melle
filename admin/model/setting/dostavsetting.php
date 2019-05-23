<?php
class ModelSettingDostavsetting extends Model {
	
		public function getSettingValue($key, $store_id = 0) {
			$query = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");
	
			if ($query->num_rows) {
				return $query->row['value'];
			} else {
				return 'notfaund';	
			}
		}	
	
	
	public function renameSetting($key) {
		$this->db->query("UPDATE " . DB_PREFIX . "setting SET `key` = '" . "total_". $key."'  WHERE `key` = '".$key."'");
	}
}
