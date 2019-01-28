<?php
class ModelApiImport1CCustomer extends Model
{
    public function addOldUsers()
    {
        $this->load->model('account/customer');

        $q = $this->db->query("SELECT * FROM " . DB_PREFIX . "old_customer c
            LEFT JOIN " . DB_PREFIX . "old_customer_info ci ON (c.id = ci.user_id)");

        foreach ($q->rows as $info) {

            if (!$this->model_account_customer->getCustomerByEmail($info['email'])) {

                $ex = explode(" ", trim($info['name']));
                if (count($ex) > 1) {
                    $firstname = array_shift($ex);
                    $lastname = implode(" ", $ex);
                } else {
                    $firstname = $info['name'];
                    $lastname = '--';
                }

                $phone = filter_var(str_replace(["+7", "(", ")", "-"], ["", "", "", ""], $info['phone']),
                    FILTER_SANITIZE_NUMBER_INT);

                $customer_id = $this->model_account_customer->addCustomer(array(
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $info['email'],
                    'telephone' => $phone,
                    'password' => substr($info['password'], 0, 10),
                    'newsletter' => (bool)$info['accept'],
                    'birth' => $info['birth'],
                    'discount_card' => $info['personal'],
                ));
            }

        }
    }

}

