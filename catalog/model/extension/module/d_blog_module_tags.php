<?php
class ModelExtensionModuleDBlogModuleTags extends Model {

    public function getTags()
    {
        $query = $this->db->query("SELECT bp.tag FROM ".DB_PREFIX."bm_post_description bp");

        $tags = array();
        foreach ($query->rows as $value) {

            $tag_split = preg_split("/,/", $value['tag']);
            foreach ($tag_split as $value) {
                $tags[] = trim($value);
            }
        }
        $tags = array_unique($tags);

        return $tags;
    }

    public function TotalPostsByTag($tag)
    {
        $query = $this->db->query("SELECT count(*) as total FROM ".DB_PREFIX."bm_post_description bp WHERE bp.tag LIKE '%" . $tag . "%'");
        return $query->row['total'];
    }

}
