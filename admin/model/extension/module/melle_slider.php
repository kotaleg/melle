<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleMelleSlider extends Model
{
    private $codename = 'melle_slider';
    private $route = 'extension/module/melle_slider';

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . \melle_slider\constant::GROUP_TABLE ."` (
            `bannerGroupId` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(64) NOT NULL,
            `status` tinyint(1) NOT NULL,
            `sortOrder` int(11) NOT NULL,
            `createDate` datetime NOT NULL,
            `updateDate` datetime NOT NULL,

            PRIMARY KEY (`id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . \melle_slider\constant::IMAGE_TABLE ."` (
            `imageId` int(11) NOT NULL AUTO_INCREMENT,
            `bannerGroupId` int(11) NOT NULL,
            `languageId` int(11) NOT NULL,
            `title` varchar(64) NOT NULL,
            `link` varchar(255) NOT NULL,
            `image` varchar(255) NOT NULL,
            `type` varchar(16) NOT NULL,
            `sortOrder` int(11) NOT NULL,
            `createDate` datetime NOT NULL,
            `updateDate` datetime NOT NULL,

            PRIMARY KEY (`id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    public function dropTables()
    {
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . \melle_slider\constant::GROUP_TABLE ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . \melle_slider\constant::IMAGE_TABLE ."`");
    }

    private function log($message)
    {
        $this->log->write(strtoupper($this->codename)." :: {$message}");
    }

    public function getBannerSortOrder($bannerId)
    {
        $sortOrder = 0;
        $imagesData = $this->db->query("SELECT * FROM `". DB_PREFIX . "banner_image`
            WHERE `banner_id` = '". (int) $bannerId ."'")->rows;

        foreach ($imagesData as $img) {
            if ((int)$img['sort_order'] > $sortOrder) {
                $sortOrder = (int)$img['sort_order'];
            }
        }

        return $sortOrder;
    }

    public function prepareBannerImages($languages, $bannerImages)
    {
        $allowedName = array(
            \melle_slider\constant::BIG => true,
            \melle_slider\constant::MEDIUM => true,
            \melle_slider\constant::SMALL => true,
        );

        $this->load->model('tool/image');

        foreach ($languages as $l) {
            
            if (!isset($bannerImages[$l['language_id']])) {
                $bannerImages[$l['language_id']] = array();
            }

            $allowedLocal = $allowedName;
            foreach ($bannerImages[$l['language_id']] as $key => $imageData) {
                $imageName = utf8_strtolower($imageData['title']);
                if (!isset($allowedLocal[$imageName])) {
                    unset($bannerImages[$l['language_id']][$key]);
                } else {
                    unset($allowedLocal[$imageName]);
                }
            }

            foreach ($allowedLocal as $type => $status) {
                $bannerImages[$l['language_id']][] = array(
                    'title'      => $type,
                    'link'       => '',
                    'image'      => '',
                    'thumb'      => $this->model_tool_image->resize('no_image.png', 100, 100),
                    'sort_order' => 0,
                );
            }
        }

        return $bannerImages;
    }

    public function updateSortOrderForBanner($bannerId, $sortOrder)
    {
        $this->db->query("UPDATE `". DB_PREFIX . "banner_image`
            SET `sort_order` = '". (int) $sortOrder ."'
            WHERE `banner_id` = '" . (int) $bannerId . "'");
    }

    public function getSlideshowModules($bannerId)
    {
        $this->load->model('setting/module');
        $allModules = $this->model_setting_module->getModulesByCode('slideshow');

        return array_map(function($module) use ($bannerId, $allModules) {
            $selected = false;
            foreach ($allModules as $moduleData) {
                $moduleSettings = json_decode($moduleData['setting'], true);

                if (isset($moduleSettings['selected_banner'])
                && is_array($moduleSettings['selected_banner'])) {
                    if (in_array($bannerId, $moduleSettings['selected_banner'])) {
                        $selected = true;
                    }
                }
            }

            return array(
                'module_id' => $module['module_id'],
                'name' => $module['name'],
                'selected' => $selected,
            );
        }, $allModules);
    }
    

    private function isBannerInSlideshowModule($bannerId, $slideshowModuleId)
    {
        $this->load->model('setting/module');

        $moduleSettings = $this->model_setting_module->getModule($slideshowModuleId);
        if (isset($moduleSettings['selected_banner'])
        && is_array($moduleSettings['selected_banner'])) {
            if (in_array($bannerId, $moduleSettings['selected_banner'])) {
                return true;
            }
        }
    }

    public function addBannerToSlideshow($bannerId, $connectedSlider)
    {
        $this->load->model('setting/module');

        $moduleData = $this->model_setting_module->getModule($connectedSlider);
        if (isset($moduleData['selected_banner'])
        && is_array($moduleData['selected_banner'])) {
            if (!in_array($bannerId, $moduleData['selected_banner'])) {
                $moduleData['selected_banner'][] = $bannerId;
            }

            $this->model_setting_module->editModule($connectedSlider, $moduleData);
        }
    }
}
