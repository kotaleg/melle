<?php
/*
 *  location: admin/model
 *
 */
class ModelExtensionProPatchDb extends Model
{
    public function isTableExist($table_name)
    {
        $query = $this->db->query("SHOW TABLES LIKE '". DB_PREFIX . $this->db->escape($table_name) . "';");
        if ($query->row) {
            return true;
        } else {
            return false;
        }
    }

    public function sqlOnDuplicateUpdateBuilder($table_name, $update_data)
    {
        if (!is_array($update_data) || empty($update_data)) { return false; }

        $sql = "INSERT INTO `". DB_PREFIX .$this->db->escape($table_name) ."` ";

        $column_names = array_keys($update_data);
        $last_column = array_pop($column_names);

        $sql .= "(";
        foreach ($update_data as $c => $v) {
            $coma = ($c === $last_column) ? '' : ',';
            $sql .= "`{$c}`{$coma}";
        }
        $sql .= ") VALUES(";

        foreach ($update_data as $c => $v) {
            $value = isset($v['data']) ? $v['data'] : $v;
            $coma = ($c === $last_column) ? '' : ',';
            $sql .= "'". $this->db->escape($value) ."'{$coma}";
        }

        $sql .= ") ON DUPLICATE KEY UPDATE ";

        $update_columns = array();
        foreach ($update_data as $c => $v) {
            if (isset($v['update'])) {
                if ($v['update'] == true) {
                    $update_columns[$c] = true;
                }
            } else {
                $update_columns[$c] = true;
            }
        }

        $column_names = array_keys($update_columns);
        $last_column = array_pop($column_names);

        foreach ($update_columns as $c => $v) {
            $coma = ($c === $last_column) ? '' : ',';
            $sql .= "`{$c}` = VALUES(`{$c}`){$coma}";
        }

        return $sql;
    }
}