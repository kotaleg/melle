<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModuleMelleBlocks extends Model
{
    private $codename = 'melle_blocks';
    private $route = 'extension/module/melle_blocks';

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function prepareBlocks($moduleId, $height)
    {
        $blocks = array();

        $this->load->model('tool/base');
        $this->load->model('tool/image');

        foreach ($this->getBlocks($moduleId) as $b) {

            if (isset($b['image']) && is_file(DIR_IMAGE . $b['image'])) {
                list($imageWidth, $imageHeight) = getimagesize(DIR_IMAGE . $b['image']);
                $b['image'] = $this->model_tool_image->resize($b['image'], $imageWidth, $imageHeight);
            }

            if (!empty($b['link']) && mb_strlen($b['link']) > 3) {
                if (strpos($b['link'], '=') === 0) {

                    $b['link'] = ltrim($b['link'], '=');
                    $ex = explode('&', $b['link']);

                    if ($ex) {
                        $route = array_shift($ex);
                        $b['link'] = $this->url->link($route, implode('&', $ex));
                    }
                }
            }
            $b['height'] = $height;
            $blocks[] = $b;
        }

        return $blocks;
    }

    private function getBlocks($moduleId)
    {
        $q = $this->db->query("SELECT *
            FROM `". DB_PREFIX . \melle_blocks\constant::BLOCK_TABLE ."`
            WHERE `moduleId` = '" . (int)$moduleId . "'
            ORDER BY `sortOrder` ASC");

        return $q->rows;
    }
}
