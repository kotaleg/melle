<?php
class ControllerExtensionDExportImportModuleDBlogModuleCategory extends Controller {

    private $codename = 'd_export_import';
    private $route = 'extension/d_export_import_module/d_blog_module_category';
    
    private $extension = array();


    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->model($this->route);
    }

    public function repair(){
        $this->{'model_extension_'.$this->codename.'_module_d_blog_module_category'}->repairCategories();
    }
}