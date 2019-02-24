<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModuleMelle extends Controller
{
    private $codename = 'melle';
    private $route = 'extension/module/melle';
    private $type = 'module';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->route);

        $this->load->model('extension/pro_patch/url');
    }

    public function getMenu()
    {
        $this->load->model('catalog/category');
        $top_categories = $this->model_catalog_category->getCategories(0);

        $menu = array();

        foreach ($top_categories as $cat) {

            if (!$cat['top']) { continue; }

            $children = array();
            $cc = $this->model_catalog_category->getCategories($cat['category_id']);
            foreach ($cc as $cat2) {
                $children[] = array(
                    'title'     => $cat2['name'],
                    'url'       => $this->model_extension_pro_patch_url->ajax('product/category', 'path=' . $cat['category_id'] .'_'. $cat2['category_id']),
                );
            }

            $cc_add = $this->model_catalog_category->getAdditionalCats($cat['category_id']);
            foreach ($cc_add as $cat3) {
                $check = true;
                foreach ($cc as $cat2) {
                    if ($cat3['category_id'] == $cat2['category_id']) {
                        $check = false;
                        break;
                    }
                }

                if ($check) {
                    $children[] = array(
                        'title'     => $cat3['name'],
                        'url'       => $this->model_extension_pro_patch_url->ajax('product/category', 'path=' . $cat['category_id'] .'_'. $cat3['category_id']),
                    );
                }
            }

            $menu[] = array(
                'title'     => $cat['name'],
                'url'       => $this->model_extension_pro_patch_url->ajax('product/category', 'path=' . $cat['category_id']),
                'children'  => $children,
                'active'    => false,
            );
        }

        return $menu;
    }

}