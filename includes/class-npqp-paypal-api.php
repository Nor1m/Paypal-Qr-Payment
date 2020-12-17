<?php

class NPQP_PayPal_API{

    function __construct($data){
        $this->clientId     = trim( $data['api_client_id'] );
        $this->clientSecret = trim( $data['api_secret_id'] );
        $this->webhook_id   = trim( $data['paypal_webhook_id'] );
        $this->order        = isset( $data['order'] ) ? $data['order'] : false;
        $fields             = isset( $data['fields'] ) ? $data['fields'] : false;
        $order              = isset( $data['order'] ) ? $data['order'] : false;
        $this->testmode     = $data['testmode'];
        $this->paypal_url   = $this->testmode ? "https://api.sandbox.paypal.com" : "https://api.paypal.com";

        $this->accessToken  = $this->getAccessToken();

        if ( $order ) {
            $NPQP_Country_Code = new NPQP_Country_Code();
            $this->invoice_data = require_once dirname( __FILE__ ) . '/class-npqp-form-invoice-data.php';
        }
    }

    // функция получения токена
    public function getAccessToken(){
        $response = wp_remote_request( $this->paypal_url . "/v1/oauth2/token", [
            'headers' => [
                'Content-Type'    => 'application/x-www-form-urlencoded',
                'Accept-Language' => 'en_US',
                'Accept'          => 'application/json',
                'Authorization'   => 'Basic ' . base64_encode( $this->clientId . ":" . $this->clientSecret )
            ],
            'body' => [
                'grant_type' => 'client_credentials'
            ],
            'method' => 'POST'
        ] );

        if ( is_wp_error($response) ){
            return false;
        } else {
            return json_decode($response['body'])->access_token;
        }
    }

    public function getPaymentLink(){

        $data_json = $this->getJsonData();
        $result_create = $this->createDraftInvoice($data_json);

        if ( isset($result_create['status']) && $result_create['status'] == 'error' ) {
            return $result_create;
        } else if ( ! $result_create ) {
            return 'Not found result_create';
        }

        $result_send = $this->sendDraftInvoice($result_create);

        if ( ! $result_send ) {
            return false;
        }

        return $result_send;
    }

    // функция создания черновика
    public function createDraftInvoice($data){

        $result_response = $this->client('post', $this->paypal_url . '/v2/invoicing/invoices', $data);
        $result = $result_response['result'];
        $issue = $result_response['issue'];
        $success = $result_response['success'];

        if ( ! $success ) {
            // если инвойс уже существует, то добавляем к номеру уник. идент.
            if ( $issue == 'DUPLICATE_INVOICE_NUMBER' ){
                $data = json_decode( $data, 1 );
                $new_invoice_number = $data['detail']['invoice_number'] . '-' . uniqid();
                npqpLog('createDraftInvoice change invoice number', [
                    'old' => $data['detail']['invoice_number'],
                    'new' => $new_invoice_number,
                ]);
                $data['detail']['invoice_number'] = $new_invoice_number;
                $data = json_encode($data, 1);
                return $this->createDraftInvoice($data);
            }
            return $this->returnErrorMessage($result);
        } else {
            return isset(json_decode($result)->href) ? json_decode($result)->href : false;
        }
    }

    // функция отправки черновика
    public function sendDraftInvoice($href){
        $result = $this->client('post', $href . '/send');
        $result = $result['result'];
        if ( ! isset(json_decode($result)->href) ) {
            return $this->returnErrorMessage($result);
        } else {
            return json_decode($result)->href;
        }
    }

    // функция, которая возвращает ошибку из результата
    public function returnErrorMessage($result){

        if ( $result ) {
            $result = json_decode( $result, 1 );
            npqpLog('returnErrorMessage result dump', $result);
            if ( isset($result['details'][0]['description']) ) {
                return [
                    'status' => 'error',
                    'error' => $result['details'][0]['description']
                ];
            } else {
                if ( isset($result['message']) ) {
                    return [
                        'status' => 'error',
                        'error' => $result['message']
                    ];
                }
            }
        }

        return ['status' => 'error', 'error' => 'Unknown error'];
    }

    // верификация вебхука
    public function verifyWebhook(){

        $headers           = array_change_key_case(getallheaders(), CASE_UPPER);
        $transmission_id   = $headers['PAYPAL-TRANSMISSION-ID'];
        $transmission_time = $headers['PAYPAL-TRANSMISSION-TIME'];
        $cert_url          = $headers['PAYPAL-CERT-URL'];
        $auth_algo         = $headers['PAYPAL-AUTH-ALGO'];
        $transmission_sig  = $headers['PAYPAL-TRANSMISSION-SIG'];
        $webhook_id        = $this->webhook_id;
        $webhook_event     = json_decode( file_get_contents('php://input'), false );

        $data = [
            'transmission_id' => $transmission_id,
            'transmission_time' => $transmission_time,
            'cert_url' => $cert_url,
            'auth_algo' => $auth_algo,
            'transmission_sig' => $transmission_sig,
            'webhook_id' => $webhook_id,
            'webhook_event' => $webhook_event,
        ];

        $result = $this->client( 'post', $this->paypal_url . '/v1/notifications/verify-webhook-signature', json_encode($data) );

        if ( isset($result['result']) && $result['success'] ) {
            $verification_status = json_decode($result['result'], true);
            $verification_status = $verification_status['verification_status'];
        }

        return $verification_status == 'SUCCESS' ? 'SUCCESS' : 'FAILURE';
    }

    // функция отправки запросов на paypal API
    public function client($method, $where, $data = false){

        $accessToken = $this->accessToken;

        $request_data = [
            'headers' => [
                'Content-Type'          => 'application/json;',
                'Accept'                => 'application/json;',
                'Content-Length'        => strlen($data),
                'Authorization'         => 'Bearer ' . $accessToken
            ],
            'method' => strtoupper($method)
        ];

        if ( $data ) $request_data['body'] = $data;

        $response = wp_remote_request( $where, $request_data );
        $message = null;
        $issue = null;
        $code = 200;

        if ( isset($response['body']) && $response['body'] ) {

            $body = json_decode($response['body']);

            if ( isset($body->details) && isset($body->debug_id) ) {
                if ( isset( $body->details[0]->issue ) ) {
                    $issue = $body->details[0]->issue;
                }
                npqpLog('client error', [
                    'where' => $where,
                    'details' => $body->details,
                    'issue' => $issue
                ]);
            }
        }

        if ( isset($response['body']) && $response['body'] ) {
            $code = $response['response']['code'];
        }
        if ( isset($response['message']) && $response['message'] ) {
            $message = $response['response']['message'];
        }

        $success = in_array($code, [200,201,202,203,204,205,206,207,208,226]);

        if ( is_wp_error($response) ) {
            $return_data = [
                'success' => false,
                'code'   => 400,
                'message' => $message,
                'result' => false,
                'issue' => $issue
            ];
        } else {
            $return_data = [
                'success' => $success,
                'code'   => $code,
                'message' => $message,
                'result' => $response['body'],
                'issue' => $issue
            ];
        }

        /*npqpLog('client', [
            'where' => $where,
            'data' => $return_data
        ]);*/

        return $return_data;
    }

    public function getJsonData(){
        return $this->invoice_data ? json_encode($this->invoice_data, 1) : false;
    }

}