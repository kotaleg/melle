<?php
/*
 *  location: admin/model
 *
 */
class ModelExtensionProPatchPermission extends Model
{
    const PERMISSION_MODIFY = 'modify';

    public function addPermission($codename, $force = False)
    {
        $this->load->model('user/user_group');
        $this->load->model('extension/pro_patch/user');

        if (is_array($codename)) {
            $routes = $codename;
        } else {
            $routes = array(
                "extension/module/{$codename}",
            );
        }

        if ($force) {
            foreach ($routes as $r) {
                $this->model_user_user_group->removePermission($this->model_extension_pro_patch_user->getGroupId(), 'access', $r);
                $this->model_user_user_group->removePermission($this->model_extension_pro_patch_user->getGroupId(), 'modify', $r);
            }
        }

        foreach ($routes as $r) {
            $this->model_user_user_group->addPermission($this->model_extension_pro_patch_user->getGroupId(), 'access', $r);
            $this->model_user_user_group->addPermission($this->model_extension_pro_patch_user->getGroupId(), 'modify', $r);
        }
    }

    public function validateRoute($route, $permission = self::PERMISSION_MODIFY)
    {
        $json = array();

        $language = new Language();
        $language->load($route);

        if (!$this->user->hasPermission($permission, $route)) {
            $json['error'][] = $language->get('error_permission');
        }

        return $json;
    }
}