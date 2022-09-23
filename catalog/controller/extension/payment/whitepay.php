<?
class ControllerExtensionPaymentWhitePay extends Controller 
{

     private $api = 'https://pay.whitepay.com/private-api/crypto-orders/eapteka';

     public function index() {
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_loading'] = $this->language->get('text_loading');
        $data['continue'] = $this->url->link('checkout/success');
        
        return $this->load->view('extension/payment/whitepay', $data);
    }

    public function confirm() {

        if ($this->session->data['payment_method']['code'] == 'whitepay') {
            $this->load->model('checkout/order');
            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('whitepay_order_status_id'));
        }
    }

    private function makeRequest($data){


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->api);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json' , 'Authorization: Bearer ' . $this->config->get('whitepay_api_key')]); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_VERBOSE, true);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    private function updateOrderFieldsData($order_id, $data){
        $this->db->query("
            UPDATE `oc_order` SET 
            whitepay_order_id   = '" . $this->db->escape($data['whitepay_order_id']) . "',          
            whitepay_href       = '" . $this->db->escape($data['whitepay_href']) . "',  
            whitepay_json       = '" . $this->db->escape($data['whitepay_json']) . "'
            WHERE order_id      = '" . (int)$order_id . "'");    
    }

    private function updateWhitePayJSON($order_id, $json){
        $this->db->query("
            UPDATE `oc_order` SET 
            whitepay_json       = '" . $this->db->escape($json) . "'
            WHERE order_id      = '" . (int)$order_id . "'");  
    }

    private function updateWhitePayStatus($order_id, $status){
        $this->db->query("
            UPDATE `oc_order` SET 
            whitepay_status       = '" . $this->db->escape($status) . "'
            WHERE order_id      = '" . (int)$order_id . "'");  
    }

    private function updateWhitePayAmount($order_id, $amount){
        $this->db->query("
            UPDATE `oc_order` SET 
            whitepay_amount       = '" . (float)$amount . "'
            WHERE order_id      = '" . (int)$order_id . "'");  
    }

    public function payment(){
        $this->load->model('checkout/order');

        $order_id = false;

        if (!empty($this->session->data['order_id'])){
            $order_id = (int)$this->session->data['order_id'];
        }

        if (!$order_id && !empty($this->session->data['recent_order_id'])){
            $order_id = (int)$this->session->data['recent_order_id'];
        }

        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ($order_info && $order_info['payment_code'] == 'whitepay' && !in_array($order_info['order_status_id'], [5, 8, 9, 11])){
            $result = $this->makeRequest(['amount' => (string)(float)$order_info['total'], 'currency' => (string)'UAH', 'external_order_id' => (string)(int)$order_info['order_id']]);

            $success = false;
            $data = [
                'whitepay_json'         => $result,
                'whitepay_order_id'     => '',
                'whitepay_href'         => ''
            ];


            if ($json = json_decode($result, true)){            
                if (!empty($json['order']['id']) && !empty($json['order']['acquiring_url'])){
                    $data['whitepay_order_id']  = $json['order']['id'];
                    $data['whitepay_href']      = $json['order']['acquiring_url'];
                    $success                    = true;
                }
            }

            $this->updateOrderFieldsData($order_id, $data);
            $this->updateWhitePayStatus($order_id, 'INIT');

            if ($success){
                //$this->response->setOutput(json_encode(['status' => 'ok', 'acquiring_url' => $data['whitepay_href']]));
                header('HTTP/1.1 302 Found');
                header('Location: ' . $data['whitepay_href']);
                return;
            } else {
                $this->response->setOutput(json_encode(['status' => 'fail', 'msg' => 'wfp fail']));
            }
        } else {
            $this->response->setOutput(json_encode(['status' => 'fail', 'msg' => 'no order']));
        }
    }

    public function addOrderToQueue($order_id){
        $this->load->model('checkout/order');
        $this->load->model('account/order');

        if (!$order_id) {
            die();
        }                       

        $order_info = $this->model_checkout_order->getOrder($order_id);
        $order_products = $this->model_checkout_order->getOrderProducts($order_id);

        if ($order_info && $order_products) {
            $this->db->query("INSERT INTO  `" . DB_PREFIX . "order_queue` SET order_id = '" . (int)$order_id . "', date_added = NOW()");
        }
    }

    public function callback_45ceb56eb17feeb0() {
        ini_set('display_errors', true);
        ini_set('error_reporting', E_ALL);

        $this->load->model('checkout/order');
        $whitepay_log = new Log('whitepay_callback.txt');
        $whitepay_log->write($this->request);

        $payload = file_get_contents('php://input');

        if (!$json = json_decode($payload, true)){
            die('json parse error');
        }

        $payloadJson = json_encode($json);
        $whitepay_log->write($payloadJson);
        $calculatedSignature    = hash_hmac('sha256', $payloadJson, $this->config->get('whitepay_webhook_key'));        

        $wpSignature = false;
        $wpSecretKey = false;

        foreach ($this->request->server as $header => $value){           
            if ($header == 'HTTP_SIGNATURE'){
                $wpSignature = $value;
            }

            if ($header == 'HTTP_X_SECRET_KEY'){
                $wpSecretKey = $value;
            }
        }

        if (!$wpSignature && !$wpSecretKey){
            die('no signature');
        }

        if ($wpSignature != $calculatedSignature && $wpSecretKey != $this->config->get('whitepay_webhook_key')){
            die('signature mismatch');
        }

        if (empty($json['order'])){
            die('that is not order payload');
        }

        if (empty($json['order']['external_order_id'])){
            die('no external_order_id');
        }

        if (!$order_info = $this->model_checkout_order->getOrder($json['order']['external_order_id'])){                   
            die('no order with external_order_id');
        }

        $this->updateWhitePayJSON($order_info['order_id'], json_encode($json));
        $this->updateWhitePayAmount($order_info['order_id'], (float)$json['order']['received_total']);


        if ($json['order']['status'] == 'INIT'){
            if ((string)$order_info['whitepay_order_id'] == (string)$json['order']['id'] && !in_array((int)$order_info['order_status_id'], [8, (int)$this->config->get('whitepay_success_order_status_id')])){ 

                 $this->updateWhitePayStatus($order_id, 'INIT');

                $this->model_checkout_order->addOrderHistory($order_info['order_id'], 12);
                $this->addOrderToQueue($order_info['order_id']);

                echo('SUCCESSFULLY INIT ORDER ' . $order_info['order_id']);
                die();

            }
        }
        
        if ($json['order']['status'] == 'DECLINED' || $json['order']['status'] == 'CANCELED'){
            //Неудачная попытка оплаты только в том случае, если у нас есть совпадение что это текущая транзакция
            if ((string)$order_info['whitepay_order_id'] == (string)$json['order']['id'] && !in_array((int)$order_info['order_status_id'], [8, (int)$this->config->get('whitepay_success_order_status_id')])){

                 $this->updateWhitePayStatus($order_id, 'DECLINED');

                $this->model_checkout_order->addOrderHistory($order_info['order_id'], 10);
                $this->addOrderToQueue($order_info['order_id']);
 
                echo('SUCCESSFULLY DECLINED ORDER');
                die();
            }
        }

        //А вот если приходит информация о полной оплате, то мы в любом случае подтверждаем, даже если он отменен! Хер знает, когда этот коллбек придет
        if ($json['order']['status'] == 'COMPLETE'){    
            if (!in_array((int)$order_info['order_status_id'], [(int)$this->config->get('whitepay_success_order_status_id')])){
                
                 $this->updateWhitePayStatus($order_id, 'COMPLETE');

                $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('whitepay_success_order_status_id'), $comment = 'Оплата WhitePay', $notify = true);
                $this->addOrderToQueue($order_info['order_id']);

                echo('SUCCESSFULLY COMPLETED ORDER');
                die();
            } else {
                echo('CURRENT_STATUS ' . $order_info['order_status_id']);
                die();
            }
        }

        echo('UNKNOWN STATUS');
        die();
    }
}