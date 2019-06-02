<?php
class ModelToolOpt extends Model
{
    public function isOpt()
    {
        if (defined('DIR_OPT')) {
            if (is_file(DIR_OPT.'index.php')
            && $this->getOptLoginLink()) {
                return true;
            }
        }
    }

    public function getOptLoginLink()
    {
        if (defined('HTTP_OPT_ADMIN')) {
            $this->load->model('user/api');

            $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

            if ($api_info && isset($api_info['key'])) {
                return HTTP_OPT_ADMIN .
                    "index.php?route=common/login&ivantoken={$api_info['key']}&user={$this->user->getUserName()}";
            }
        }
    }

    public function getMainLoginLink()
    {
        if (defined('HTTP_MAIN_ADMIN')) {
            $this->load->model('user/api');

            $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

            if ($api_info && isset($api_info['key'])) {
                return HTTP_MAIN_ADMIN .
                    "index.php?route=common/login&ivantoken={$api_info['key']}&user={$this->user->getUserName()}";
            }
        }
    }

    public function isValidToken($token)
    {
        if ($token) {
            $this->load->model('user/api');
            if ($this->model_user_api->getApiByKey($token)) {
                return true;
            }
        }

        return false;
    }

    public function loginAsUser($username)
    {
        $this->load->model('user/user');
        $u = $this->model_user_user->getUserByUsername($username);
        if ($u && isset($u['password']) && isset($u['username'])) {
            return $this->user->login($u['username'], $u['password'], true);
        }

        return false;
    }

    public function isMain()
    {
        if (defined('DIR_MAIN')) {
            if (is_file(DIR_MAIN.'index.php')) {
                return true;
            }
        }
    }
}

