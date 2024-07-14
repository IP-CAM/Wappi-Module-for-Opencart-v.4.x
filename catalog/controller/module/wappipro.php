<?php
namespace Opencart\Catalog\Controller\Extension\WappiproOc4x\Module;

use Opencart\System\Engine\Controller;

class WappiPro extends Controller 
{

    public function status_change($route, $data)
    {
        $orderStatusId = $data[1];
        $orderId       = $data[0];

        $this->load->model('setting/setting');
        $this->load->model('checkout/order');

        $settings = $this->model_setting_setting->getSetting('wappipro');

        $order        = $this->model_checkout_order->getOrder($orderId);
        $statusName   = $this->getStatusName($orderStatusId);
        $isActive     = $settings["wappipro_active"];
        $isSelfSendingActive     = $settings["wappipro_self_sending_active"];

        if ($this->isModuleEnabled() && !empty($isActive) && !empty($statusName)) {

            if (strpos($statusName, "Canceled Reversal") !== false) {
                $status_name = "canceled_reversal";
            } else {
                $status_name = strtolower($statusName);
            }

            $isAdminSend = $settings["wappipro_admin_" . $status_name . "_active"];

            $statusActivate = $settings["wappipro_" . $status_name . "_active"];
            $statusMessage  = $settings["wappipro_" . $status_name . "_message"];

            if (!empty($statusActivate) && !empty($statusMessage)) {

                $replace = [
                    '{order_number}'       => $order['order_id'],
                    '{order_date}'         => $order['date_added'],
                    '{order_total}'        => round($order['total'] * $order['currency_value'], 2) . ' ' . $order['currency_code'],
                    '{billing_first_name}' => $order['payment_firstname'],
                    '{billing_last_name}'  => $order['payment_lastname'],
                    '{firstname}' => $order['firstname'],
                    '{lastname}'  => $order['lastname'],
                    '{shipping_method}'    => $order['shipping_method']['name']
                ];

                foreach ($replace as $key => $value) {
                    $statusMessage = str_replace($key, $value, $statusMessage);
                }

                $apiKey   = $settings["wappipro_apiKey"];
                $username = $settings["wappipro_username"];

                if (!empty($apiKey)) {

                    $platform = $this->model_setting_setting->getSetting('wappipro_platform')['wappipro_platform'];

                    $req = array();
                    $req['postfields'] = json_encode(array(
                        'recipient' => $order['telephone'],
                        'body' => $statusMessage,
                    ));

                    $req['header'] = array(
                        "accept: application/json",
                        "Authorization: " .  $apiKey,
                        "Content-Type: application/json",
                    );

                    $req['url'] = 'https://wappi.pro/'. $platform . 'api/sync/message/send?profile_id=' . $username;

                    if (!empty($isSelfSendingActive)) {

                        $wappipro_self_phone = $this->model_setting_setting->getSetting('wappipro_test')["wappipro_test_phone_number"];

                        if (!empty($wappipro_self_phone)) {

                            if (!empty($isAdminSend)) {
                                $req_self = array();
                                $req_self['postfields'] = json_encode(array(
                                    'recipient' => $wappipro_self_phone,
                                    'body' => $statusMessage,
                                ));

                                $req_self['header'] = array(
                                    "accept: application/json",
                                    "Authorization: " .  $apiKey,
                                    "Content-Type: application/json",
                                );

                                $req_self['url'] = 'https://wappi.pro/'. $platform . 'api/sync/message/send?profile_id=' . $username;
                                $response = json_decode($this->curlito(false, $req_self), true);
                            }
                        }
                    }

                    try {
                        $response = json_decode($this->curlito(false, $req), true);
                    } catch (Exception $e) {
                        var_dump($e->getMessage());
                        die();
                    }
                }
            }
        }
    }

    /**
     * @param $statusId
     *
     * @return false|mixed
     */
    public function getStatusName($statusId)
    {
        $sql = sprintf(
            "SELECT os.name FROM %sorder_status os WHERE os.order_status_id = %s AND os.language_id = 1",
            DB_PREFIX,
            $statusId
        );
        $order_status = $this->db->query($sql);

        if ($order_status->num_rows) {
            return $order_status->row['name'];
        }
        return false;
    }

    public function isModuleEnabled()
    {
        $sql    = "SELECT * FROM " . DB_PREFIX . "extension WHERE code = 'wappipro'";
        $result = $this->db->query($sql);
        if ($result->num_rows) {
            return true;
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
}
