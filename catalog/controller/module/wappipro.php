<?php
namespace Opencart\Catalog\Controller\Extension\WappiproOc4x\Module;

use Opencart\System\Engine\Controller;

class WappiPro extends Controller 
{

    public function status_change($route, $data) {
        $orderStatusId = $data[1];
        $orderId       = $data[0];

        $this->load->model('setting/setting');
        $this->load->model('checkout/order');
        $this->load->model('localisation/order_status'); 

        $order = $this->model_checkout_order->getOrder($orderId);
        $statusName = $this->getStatusName($orderStatusId); 
        $settings = $this->model_setting_setting->getSetting('wappipro');
        $isSelfSendingActive = $settings["wappipro_admin_". $orderStatusId . "_active"];

        if ($this->isModuleEnabled() && !empty($statusName)) {
            $statusActivate = $settings["wappipro_" . $orderStatusId . "_active"];
            $statusMessage = $settings["wappipro_" . $orderStatusId . "_message"];
            
            if (!empty($statusActivate) && !empty($statusMessage)) {
                $replace = [
                    '{order_number}' => $order['order_id'],
                    '{order_date}' => $order['date_added'],
                    '{order_total}' => round($order['total'] * $order['currency_value'], 2) . ' ' . $order['currency_code'],
                    '{order_comment}' => $order['comment'],
                    '{billing_first_name}' => $order['payment_firstname'] ?? '',
                    '{billing_last_name}' => $order['payment_lastname'] ?? '',
                    '{lastname}' => $order['lastname'],
                    '{firstname}' => $order['firstname'],
                    '{shipping_method}' => $order['shipping_method']['name'] ?? '',
                ];
                
                foreach ($replace as $key => $value) {
                    $statusMessage = str_replace($key, $value, $statusMessage);
                }
                
                $apiKey = $settings['wappipro_apiKey'];
                $username = $settings['wappipro_username'];

                if (!empty($apiKey)) {

                    $platform = ($this->model_setting_setting->getSetting('wappipro_platform'))['wappipro_platform'];

                    $req = [
                        'postfields' => json_encode([
                            'recipient' => $order['telephone'],
                            'body' => $statusMessage,
                        ]),
                        'header' => [
                            "accept: application/json",
                            "Authorization: " .  $apiKey,
                            "Content-Type: application/json",
                        ],
                        'url' => 'https://wappi.pro/' . $platform . 'api/sync/message/send?profile_id=' . $username,
                    ];
                    if ($isSelfSendingActive === 'true') {
                        $wappipro_self_phone = ($this->model_setting_setting->getSetting('wappipro_test'))["wappipro_test_phone_number"];
                        if (!empty($wappipro_self_phone)) {
                            $req_self = [
                                'postfields' => json_encode([
                                    'recipient' => $wappipro_self_phone,
                                    'body' => $statusMessage,
                                ]),
                                'header' => [
                                    "accept: application/json",
                                    "Authorization: " .  $apiKey,
                                    "Content-Type: application/json",
                                ],
                                'url' => 'https://wappi.pro/' . $platform . 'api/sync/message/send?profile_id=' . $username,
                            ];
                            $response = json_decode($this->curlito(false, $req_self), true);
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

    public function isModuleEnabled(): bool {
        $sql = "SELECT * FROM " . DB_PREFIX . "extension WHERE code = 'wappipro'";
        $result = $this->db->query($sql);
        return $result->num_rows > 0;
    }

    private function curlito(bool $wait, array $req, string $method = ''): string {
        $curl = curl_init();
        $option = [
            CURLOPT_URL => $req['url'],
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $req['postfields'],
            CURLOPT_HTTPHEADER => $req['header'],
        ];

        if ($wait) {
            $option[CURLOPT_TIMEOUT] = 30;
        } else {
            $option[CURLOPT_TIMEOUT_MS] = 5000;
            $option[CURLOPT_HEADER] = 0;
        }

        curl_setopt_array($curl, $option);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            error_log($err . PHP_EOL, 3, DIR_LOGS . "wappi-errors.log");
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    /**
     * @param int $statusId
     * @return string|false
     */
    public function getStatusName(int $statusId) {
        $sql = sprintf(
            "SELECT os.name FROM %sorder_status os WHERE os.order_status_id = %d AND os.language_id = %d",
            DB_PREFIX,
            (int)$statusId,
            (int)$this->config->get('config_language_id')
        );
        $order_status = $this->db->query($sql);

        if ($order_status->num_rows) {
            return $order_status->row['name'];
        }
        return false;
    }
}
