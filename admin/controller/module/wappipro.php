<?php

namespace Opencart\Admin\Controller\Extension\WappiproOc4x\Module;

use Opencart\System\Engine\Controller;

class Wappipro extends Controller
{
    private $error = [];
    private $code = ['wappipro_test', 'wappipro'];
    public $testResult = true;
    private $fields_test = [
        "wappipro_test_phone_number" => [
            "label" => "Phone Number",
            "type" => "isPhoneNumber",
            "value" => "",
            "validate" => true,
        ],
    ];
    private $fields = [
        "wappipro_username" => ["label" => "Username", "type" => "isEmpty", "value" => "", "validate" => true],
        "wappipro_apiKey" => ["label" => "API Key", "type" => "isEmpty", "value" => "", "validate" => true],
        "wappipro_active" => ["value" => ""],

        "wappipro_canceled_active" => ["value" => ""],
        "wappipro_canceled_message" => ["value" => ""],

        "wappipro_canceled_reversal_active" => ["value" => ""],
        "wappipro_canceled_reversal_message" => ["value" => ""],

        "wappipro_self_sending_active" => ["value" => ""],

        "wappipro_chargeback_active" => ["value" => ""],
        "wappipro_chargeback_message" => ["value" => ""],

        "wappipro_complete_active" => ["value" => ""],
        "wappipro_complete_message" => ["value" => ""],

        "wappipro_denied_active" => ["value" => ""],
        "wappipro_denied_message" => ["value" => ""],

        "wappipro_refunded_active" => ["value" => ""],
        "wappipro_refunded_message" => ["value" => ""],

        "wappipro_expired_active" => ["value" => ""],
        "wappipro_expired_message" => ["value" => ""],

        "wappipro_failed_active" => ["value" => ""],
        "wappipro_failed_message" => ["value" => ""],

        "wappipro_pending_active" => ["value" => ""],
        "wappipro_pending_message" => ["value" => ""],

        "wappipro_processed_active" => ["value" => ""],
        "wappipro_processed_message" => ["value" => ""],

        "wappipro_processing_active" => ["value" => ""],
        "wappipro_processing_message" => ["value" => ""],

        "wappipro_reversed_active" => ["value" => ""],
        "wappipro_reversed_message" => ["value" => ""],

        "wappipro_shipped_active" => ["value" => ""],
        "wappipro_shipped_message" => ["value" => ""],

        "wappipro_voided_active" => ["value" => ""],
        "wappipro_voided_message" => ["value" => ""],

        "wappipro_admin_voided_active" => ["value" => ""],
        "wappipro_admin_shipped_active" => ["value" => ""],
        "wappipro_admin_reversed_active" => ["value" => ""],
        "wappipro_admin_refunded_active" => ["value" => ""],
        "wappipro_admin_processing_active" => ["value" => ""],
        "wappipro_admin_processed_active" => ["value" => ""],
        "wappipro_admin_pending_active" => ["value" => ""],
        "wappipro_admin_failed_active" => ["value" => ""],
        "wappipro_admin_expired_active" => ["value" => ""],
        "wappipro_admin_denied_active" => ["value" => ""],
        "wappipro_admin_complete_active" => ["value" => ""],
        "wappipro_admin_chargeback_active" => ["value" => ""],
        "wappipro_admin_canceled_reversal_active" => ["value" => ""],
        "wappipro_admin_canceled_active" => ["value" => ""],
    ];

    public function index(): void
    {
        if (!$this->isModuleEnabled()) {
            $this->response->redirect(
                $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
            );
            exit;
        }

        $this->load->language('extension/wappipro_oc4x/module/wappipro');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addStyle('../extension/wappipro_oc4x/admin/view/stylesheet/wappipro/wappipro.css');

        $this->load->model('setting/setting');
        $this->load->model('setting/module');
        $this->load->model('design/layout');

        $this->submitted();
        $this->loadFieldsToData($data);

        $data['error_warning'] = $this->error;

        $data['wappipro_logo'] = '../extension/wappipro_oc4x/admin/view/image/wappipro/logo.jpg';

        $data['about_title'] = $this->language->get('about_title');
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');

        $data['btn_test_text'] = $this->language->get('btn_test_text');
        $data['btn_test_placeholder'] = $this->language->get('btn_test_placeholder');
        $data['btn_test_description'] = $this->language->get('btn_test_description');
        $data['btn_test_send'] = $this->language->get('btn_test_send');

        $data['btn_wappipro_self_sending_active'] = $this->language->get('btn_wappipro_self_sending_active');

        $data['btn_apiKey_text'] = $this->language->get('btn_apiKey_text');
        $data['btn_apiKey_placeholder'] = $this->language->get('btn_apiKey_placeholder');
        $data['btn_apiKey_description'] = $this->language->get('btn_apiKey_description');
        $data['btn_duble_admin'] = $this->language->get('btn_duble_admin');

        $data['btn_username_text'] = $this->language->get('btn_username_text');
        $data['btn_username_placeholder'] = $this->language->get('btn_username_placeholder');
        $data['btn_username_description'] = $this->language->get('btn_username_description');

        $data['btn_token_save_all'] = $this->language->get('btn_token_save_all');

        $data['btn_status_order_description'] = $this->language->get('btn_status_order_description');

        $data['btn_status_order_canceled'] = $this->language->get('btn_status_order_canceled');
        $data['btn_status_order_canceled_reversal'] = $this->language->get('btn_status_order_canceled_reversal');
        $data['btn_status_order_chargebackd'] = $this->language->get('btn_status_order_chargebackd');
        $data['btn_status_order_complete'] = $this->language->get('btn_status_order_complete');
        $data['btn_status_order_denied'] = $this->language->get('btn_status_order_denied');
        $data['btn_status_order_expired'] = $this->language->get('btn_status_order_expired');
        $data['btn_status_order_failed'] = $this->language->get('btn_status_order_failed');
        $data['btn_status_order_pending'] = $this->language->get('btn_status_order_pending');
        $data['btn_status_order_processed'] = $this->language->get('btn_status_order_processed');
        $data['btn_status_order_processing'] = $this->language->get('btn_status_order_processing');
        $data['btn_status_order_refunded'] = $this->language->get('btn_status_order_refunded');
        $data['btn_status_order_reversed'] = $this->language->get('btn_status_order_reversed');
        $data['btn_status_order_shipped'] = $this->language->get('btn_status_order_shipped');
        $data['btn_status_order_voided'] = $this->language->get('btn_status_order_voided');
        $data['instructions_title'] = $this->language->get('instructions_title');

        $data['step_1'] = $this->language->get('step_1');
        $data['step_2'] = $this->language->get('step_2');
        $data['step_3'] = $this->language->get('step_3');
        $data['step_4'] = $this->language->get('step_4');
        $data['step_5'] = $this->language->get('step_5');

        $this->load->model('localisation/order_status');
        $data['order_status_list'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['wappipro_test_result'] = $this->testResult;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/wappipro_oc4x/module/wappipro', $data));
    }

    public function isModuleEnabled()
    {
        $sql = sprintf("SELECT * FROM %sextension WHERE code = 'wappipro'", DB_PREFIX);
        $result = $this->db->query($sql);
        return $result->num_rows > 0;
    }

    public function submitted()
    {
        if (!empty($_POST)) {
            if (!empty($_POST['wappipro_test'])) {
                $this->validateFields();
                if (empty($_POST['wappipro_apiKey'])) {
                    $this->error[] = ["error" => "Field api key is required for testing."];
                }

                if (empty($_POST['wappipro_username'])) {
                    $this->error[] = ["error" => "Username is required for testing."];
                }

                if (empty($this->error)) {
                    $this->saveFiledsToDB();
                    $fields = $this->getFieldsValue();
                    
                    $settings = $this->model_setting_setting->getSetting('wappipro');
                    $message = 'Test message from wappi.pro';

                    $this->_save_user($settings);
                    $platform = $this->get_platform_info($settings);
                    if ($platform !== false) {
                        if ($platform === 'wz') $platform = '';
                        else {
                            $platform = 't';
                        }
                        $this->model_setting_setting->editSetting("wappipro_platform", array('wappipro_platform' => $platform));  
                        $settings["wappipro_platform"] = $platform;
                        $result = $this->sendTestSMS(
                            $settings,
                            $fields['wappipro_test_phone_number']['value'],
                            $message
                        );
                        $this->testResult = $result;
                    } else {
                        $this->testResult = false;
                        $this->error[] = ["error" => "Site request error"];
                    }
                }
            } else {
                $this->testResult = true;
                $this->validateFields();
                if (empty($this->error)) {
                    $this->saveFiledsToDB();
                }
            }
            return true;
        }

        return false;
    }

    public function loadFieldsToData(&$data)
    {
        $settings = $this->model_setting_setting->getSetting('wappipro');

        foreach ($this->fields as $key => $value) {
            $data[$key] = isset($settings[$key]) ? $settings[$key] : '';
        }

        $data["wappipro_test_phone_number"] = $this->model_setting_setting->getSetting('wappipro_test')["wappipro_test_phone_number"];
    }

    public function saveFiledsToDB()
    {
        $fields = $this->getPostFiles();

        foreach (array_keys($fields) as $key) {
            if (isset($_POST[$key])) {
                $fields[$key] = $_POST[$key];
            } else {
                $fields[$key] = "";
            }
        }

        if (empty($_POST['wappipro_test'])) {
            $module_fields = [];
            $module_fields['module_wappipro_status'] = isset($fields['wappipro_active']) ? 'true' : 'false';
            $this->model_setting_setting->editSetting("wappipro", $module_fields);
        }

        $this->model_setting_setting->editSetting($this->getCode(), $fields);
    }

    public function validateFields()
    {
        $fields = $this->getPostFiles();

        foreach ($fields as $key => $value) {
            if (isset($value['validate'])) {
                $result = call_user_func_array(
                    [$this, $value['type']],
                    [$_POST[$key]]
                );
                if (!$result) {
                    $this->error[] = ["error" => "Field " . $value['label'] . " is required for testing."];
                }
            }
        }
    }

    public function sendTestSMS($settings, $to, $body)
    {
        $apiKey = isset($settings['wappipro_apiKey']) ? $settings['wappipro_apiKey'] : null;
        $username = isset($settings['wappipro_username']) ? $settings['wappipro_username'] : null;
        $platform = isset($settings['wappipro_platform']) ? $settings['wappipro_platform'] : null;

        if (!empty($apiKey)) {

            $req = array();
            $req['postfields'] = json_encode(array(
                'recipient' => $to,
                'body' => $body,
            ));

            $req['header'] = array(
                "accept: application/json",
                "Authorization: " .  $apiKey,
                "Content-Type: application/json",
            );
            $req['url'] = 'https://wappi.pro/'. $platform . 'api/sync/message/send?profile_id=' . $username;

            try {
                $answer = json_decode($this->curlito(false, $req), true);
                if ($answer === 200) {
                    return true;
                }
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }

    private function curlito($wait, $req, $method = '')
    {

        $curl = curl_init();
        $option = array(
            CURLOPT_URL => $req['url'],
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $req['postfields'],
            CURLOPT_HTTPHEADER => $req['header'],
        );

        if ($wait) {
            $option[CURLOPT_TIMEOUT] = 30;
        } else {
            $option[CURLOPT_TIMEOUT_MS] = 5000;
            $option[CURLOPT_HEADER] = 0;
        }

        curl_setopt_array($curl, $option);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($err) {
            error_log($err . PHP_EOL, 3, DIR_LOGS . "wappi-errors.log");
            return "cURL Error #:" . $err;
        } else {
            return $http_status;
        }
    }

    public function get_platform_info($settings) {
        $apikey = isset($settings['wappipro_apiKey']) ? $settings['wappipro_apiKey'] : null;
        $username = isset($settings['wappipro_username']) ? $settings['wappipro_username'] : null;
    
        $url = 'https://wappi.pro/api/sync/get/status?profile_id=' . urlencode($username);
        $headers = array(
            "accept: application/json",
            "Authorization: " . $apikey,
            "Content-Type: application/json",
        );
    
        $curl = curl_init();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
        );
    
        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);
    
        if ($err) {
            error_log('cURL Error: ' . $err . PHP_EOL, 3, DIR_LOGS . "wappi-errors.log");
            return "cURL Error #:" . $err;
        }
    
        if ($http_status != 200) {
            error_log('HTTP Status: ' . $http_status . ' - ' . $result . PHP_EOL, 3, DIR_LOGS . "wappi-errors.log");
            return 'HTTP Status: ' . $http_status;
        }
    
        $data = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Ошибка JSON: ' . json_last_error_msg() . PHP_EOL, 3, DIR_LOGS . "wappi-errors.log");
            return false;
        }
    
        if (isset($data['status'])) {
            error_log('Ошибка отправки GET-запроса: ' . $data['status'] . PHP_EOL, 3, DIR_LOGS . "wappi-errors.log");
            return false;
        }
        return $data['platform'];
	}

    public function _save_user($settings) {

        $apikey = isset($settings['wappipro_apiKey']) ? $settings['wappipro_apiKey'] : null;
        $username = isset($settings['wappipro_username']) ? $settings['wappipro_username'] : null;
        
        $message_json = json_encode(array(
            'url' => $_SERVER['HTTP_REFERER'],
            'module' => 'opencart2x3',
            'profile_uuid' => $username,
        ));
    
        $url = 'https://dev.wappi.pro/tapi/addInstall?profile_id=' . urlencode($username);
    
        $headers = array(
            'Accept: application/json',
            'Authorization: ' . $apikey,
            'Content-Type: application/json',
        );
    
        $curl = curl_init();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $message_json,
            CURLOPT_HTTPHEADER => $headers,
        );
    
        curl_setopt_array($curl, $options);
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
    
        if ($err) {
            error_log("Error save user to db" . PHP_EOL, 3, DIR_LOGS . "wappi-errors.log");
        } else if ($http_status !== 200) {
            error_log('HTTP Status save user: ' . $http_status . PHP_EOL, 3, DIR_LOGS . "wappi-errors.log");
        }
    }

     /**
     * @param $str
     *
     * @return bool
     */
    public static function isEmpty($str): bool
    {
        return !empty($str);
    }

    /**
     * @param $number
     *
     * @return bool
     */
    public static function isPhoneNumber($number): bool
    {
        if (empty($number)) {
            return false;
        }

        return !empty($number) && preg_match('/^[+0-9. ()-]*$/', $number);
    }

    public function getFieldsValue()
    {
        $fields = $this->getPostFiles();
        $settings = $this->model_setting_setting->getSetting('wappipro');


        if (isset($fields["wappipro_test_phone_number"])) {
            $fields["wappipro_test_phone_number"]["value"] = $this->model_setting_setting->getSetting('wappipro_test')["wappipro_test_phone_number"];
        } else {
            foreach ($fields as $key => $value) {
                $fields[$key]["value"] = $settings[$key];
            }
        }
        return $fields;
    }

    public function getPostFiles()
    {
        return (!empty($_POST['wappipro_test']) ? $this->fields_test : $this->fields);
    }

    public function getCode()
    {
        return (!empty($_POST['wappipro_test']) ? $this->code[0] : $this->code[1]);
    }

    public function install()
    {
        $this->load->model('setting/setting');
        $settings = [
            'module_wappipro_status' => '1', // Включаем модуль по умолчанию
            // другие настройки вашего модуля
        ];
        
        $this->model_setting_setting->editSetting('module_wappipro', $settings);

        $this->load->model('setting/event');

        $event_data = [
            'code'        => 'wappipro',
            'description' => '',
            'trigger'     => 'catalog/model/checkout/order/addHistory/after',
            'action'      => 'extension/wappipro/module/wappipro.status_change',
            'status'      => 1,
            'sort_order'  => 1
        ];

        $this->model_setting_event->addEvent($event_data);
    }

    public function uninstall()
    {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('wappipro');
    }
}
