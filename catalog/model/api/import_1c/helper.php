<?php
class ModelApiImport1CHelper extends Model
{
    const IMPORT_FIELD = 'import_id';

    public function isImportRecordExist($table_name, $import_id)
    {
        $query = $this->db->query("SELECT `". $this->db->escape(self::IMPORT_FIELD) ."`
            FROM `". DB_PREFIX . $this->db->escape($table_name) . "`
            WHERE `". $this->db->escape(self::IMPORT_FIELD) ."` = '". $this->db->escape($import_id) ."'");

        if ($query->num_rows) {
            return true;
        } else {
            return false;
        }
    }


}