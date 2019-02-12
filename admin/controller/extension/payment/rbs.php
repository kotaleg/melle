<?php

class ControllerExtensionPaymentRbs extends Controller
{
    private $error = array();

    /**
     * Вывод и сохранение настроек
     */
    public function index()
    {
        $this->load->language('extension/payment/rbs');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        // Сохранение настроек через POST запрос
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_rbs', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            // echo "<pre>";
            // echo $this->config->get('payment_rbs_merchantPassword');
            // echo "<pre>";
            // print_r($this->request->post);
            // die;
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', 'SSL'));
        }

        // Заголовок страницы
        $data['heading_title'] = $this->language->get('heading_title');

        // Хлебные крошки
        $data['breadcrumbs'] = array();
        array_push($data['breadcrumbs'],
            array(  // Главная
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
            ),
            array(  // Оплата
                'text' => $this->language->get('text_payment'),
                'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL')
            ),
            array(  // Оплата через {{банк}}
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/payment/rbs', 'user_token=' . $this->session->data['user_token'], 'SSL')
            )
        );

        // Кнопки сохранения и отмены
        $data['action'] = $this->url->link('extension/payment/rbs', 'user_token=' . $this->session->data['user_token'], 'SSL');
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL');

        // Кнопки
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        // Заголовок панели
        $data['text_settings'] = $this->language->get('text_settings');

        // Статус модуля: Включен/Выключен
        $data['entry_status'] = $this->language->get('status');
        $data['status_enabled'] = $this->language->get('status_enabled');
        $data['status_disabled'] = $this->language->get('status_disabled');

        if (isset($this->request->post['payment_rbs_status'])) {
            $data['payment_rbs_status'] = $this->request->post['payment_rbs_status'];
        } else {
            $data['payment_rbs_status'] = $this->config->get('payment_rbs_status');
        }
        // echo   $this->config->get('merchantPassword'); die;
        // $data['payment_rbs_status'] = $data['payment_rbs_status'] = $this->config->get('payment_rbs_status');

        // Логин мерчанта
        $data['entry_merchantLogin'] = $this->language->get('merchantLogin');
        $data['payment_rbs_merchantLogin'] = $this->config->get('payment_rbs_merchantLogin');

        // Логин мерчанта
        $data['entry_merchantPassword'] = $this->language->get('merchantPassword');
        $data['payment_rbs_merchantPassword'] = $this->config->get('payment_rbs_merchantPassword');

        // Режим работы модуля: Тестовый/Боевой
        $data['entry_mode'] = $this->language->get('mode');
        $data['mode_test'] = $this->language->get('mode_test');
        $data['mode_prod'] = $this->language->get('mode_prod');
        $data['payment_rbs_mode'] = $this->config->get('payment_rbs_mode');

        // Стадийность платежа
        $data['entry_stage'] = $this->language->get('stage');
        $data['stage_one'] = $this->language->get('stage_one');
        $data['stage_two'] = $this->language->get('stage_two');
        $data['payment_rbs_stage'] = $this->config->get('payment_rbs_stage');





        // Статус по завершении платежа
        $data['entry_order_status'] = $this->language->get('entry_order_status');

        if (isset($this->request->post['payment_rbs_order_status_id'])) {
            $data['payment_rbs_order_status_id'] = $this->request->post['payment_rbs_order_status_id'];
        } else {
            $data['payment_rbs_order_status_id'] = $this->config->get('payment_rbs_order_status_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();


        // Логирование
        $data['entry_logging'] = $this->language->get('logging');
        $data['logging_enabled'] = $this->language->get('logging_enabled');
        $data['logging_disabled'] = $this->language->get('logging_disabled');
        $data['payment_rbs_logging'] = $this->config->get('payment_rbs_logging');

        // Валюта
        $data['entry_currency'] = $this->language->get('currency');
        $data['currency_list'] = array_merge(
            array(
                array(
                    'numeric' => 0,
                    'alphabetic' => 'По умолчанию'
                )
            ), // Валюта по умолчанию в платежном шлюзе
            $this->getCurrencies()  // Список валют
        );
        $data['payment_rbs_currency'] = $this->config->get('payment_rbs_currency');


        // Передача корзины: Включена/Выключена
        $data['entry_ofdStatus'] = $this->language->get('entry_ofdStatus');
        $data['payment_rbs_ofd_status'] = $this->config->get('payment_rbs_ofd_status');
        $data['ofd_enabled'] = $this->language->get('ofd_enabled');
        $data['ofd_disabled'] = $this->language->get('ofd_disabled');

        // Системы налогообложения
        $data['entry_taxSystem'] = $this->language->get('entry_taxSystem');
        $data['taxSystem_list'] = $this->getTaxSystemList();
        $data['payment_rbs_taxSystem'] = $this->config->get('payment_rbs_taxSystem');

        // Ставка НДС по умолчанию
        $data['entry_taxType'] = $this->language->get('entry_taxType');
        $data['taxType_list'] = $this->getTaxTypeList();
        $data['payment_rbs_taxType'] = $this->config->get('payment_rbs_taxType');

        // Хедер, футер, левое меню для отрисовки страницы настроек модуля
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        // Рендеринг шаблона
        $this->response->setOutput($this->load->view('extension/payment/rbs', $data));
    }

    /**
     * Валидация данных.
     * В данном случае проверка прав на редактирование настроек платежного модуля.
     * @return bool
     */
    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/rbs')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }

    /**
     * Список валют в ISO 4217
     * @return array
     */
    private function getCurrencies()
    {
        return [
            [
                'numeric' => 643,
                'alphabetic' => 'RUR'
            ],
            [
                'numeric' => 810,
                'alphabetic' => 'RUB'
            ],
            [
                'numeric' => 840,
                'alphabetic' => 'USD'
            ],
            [
                'numeric' => 978,
                'alphabetic' => 'EUR'
            ],
        ];
    }


    /**
     * Список Ситем налогообложения
     * @return array
     */
    private function getTaxTypeList()
    {
        return [
            [
                'numeric' => 0,
                'alphabetic' => 'Без НДС'
            ],
            [
                'numeric' => 1,
                'alphabetic' => 'НДС по ставке 0%'
            ],
            [
                'numeric' => 2,
                'alphabetic' => 'НДС чека по ставке 10%'
            ],
            [
                'numeric' => 3,
                'alphabetic' => 'НДС чека по ставке 18%'
            ],
            [
                'numeric' => 4,
                'alphabetic' => 'НДС чека по расчетной ставке 10/110'
            ],
            [
                'numeric' => 5,
                'alphabetic' => 'НДС чека по расчетной ставке 10/118'
            ],
        ];
    }


    /**
     * Список Ситем налогообложения
     * @return array
     */
    private function getTaxSystemList()
    {
        return [
            [
                'numeric' => 0,
                'alphabetic' => 'Общая'
            ],
            [
                'numeric' => 1,
                'alphabetic' => 'Упрощенная, доход'
            ],
            [
                'numeric' => 2,
                'alphabetic' => 'Упрощенная, доход минус расход'
            ],
            [
                'numeric' => 3,
                'alphabetic' => 'Eдиный налог на вменённый доход'
            ],
            [
                'numeric' => 4,
                'alphabetic' => 'Единый сельскохозяйственный налог'
            ],
            [
                'numeric' => 5,
                'alphabetic' => 'Патентная система налогообложения'
            ],
        ];
    }
}