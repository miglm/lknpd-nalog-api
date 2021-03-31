<?php

class LkNpdNalogApi
{
    private $api_url;
    private $username;
    private $password;
    private $inn;
    private $timezone;
    private $response;
    private $sourceDeviceId;
    private $token;
    private $receiptUuid;
    private $receiptUrlPrint;
    private $receiptUrlJson;
    private $error;
    private $errorMessage;
    private $errorExceptionMessage;

    /**
     * LkNpdNalogApi constructor.
     * @param $username
     * @param $password
     * @param string $timezone
     */
    public function __construct($username, $password, $timezone = 'Europe/Moscow')
    {
        $this->api_url = 'https://lknpd.nalog.ru/api/v1';

        $this->username = $username;
        $this->password = $password;
        $this->timezone = $timezone;

        $this->sourceDeviceId = $this->_createDeviceId();
    }

    /**
     * @param $name
     * @return bool
     */
    public function __get($name)
    {
        switch ($name) {
            case 'error':
                return $this->error;
            case 'errorMessage':
                return $this->errorMessage;
            case 'errorExceptionMessage':
                return $this->errorExceptionMessage;
            case 'receiptUuid':
                return $this->receiptUuid;
            case 'receiptUrlPrint':
                return $this->receiptUrlPrint;
            case 'receiptUrlJson':
                return $this->receiptUrlJson;
            default:
                return $this->response;
        }
    }

    /**
     * @return string
     */
    private function _createDeviceId(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < 20; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * @return string
     */
    private function _getUtcTime(): string
    {
        $datetime = new DateTime('now');
        $datetime->setTimeZone(new DateTimeZone($this->timezone));
        $utcTime = $datetime->format('c');
        $this->utcTime = $utcTime;

        return $utcTime;
    }

    private function _abort($reason, $exception = null){
        $this->error = true;
        $this->errorMessage = $reason;
        $this->errorExceptionMessage = $exception;
    }

    /**
     * @param $args
     * @throws HttpException
     */
    public function createReceipt($args){
        $this->makeAuth();

        $args = (object) $args;

        if (!$this->error){
            $payload = [
                'operationTime' => $this->_getUtcTime(),
                'requestTime' => $this->_getUtcTime(),
                'services' => [
                    [
                        'name' => $args->name,
                        'amount' => $args->amount,
                        'quantity' => 1,
                    ]
                ],
                'totalAmount' => $args->amount,
                'client' => [
                    'contactPhone' => $args->clientContactPhone || null,
                    'displayName' => $args->clientDisplayName || null,
                    'inn' => null,
                    'incomeType' => 'FROM_INDIVIDUAL'
                ],
                'paymentType' => 'CASH',
                'ignoreMaxTotalIncomeRestriction' => false,
            ];

            $this->_makeQuery('createReceipt', $payload);
        }
    }

    /**
     * @param $args
     * @throws HttpException
     */
    public function cancelReceipt($args){
        $this->makeAuth();

        $args = (object) $args;

        $reasonComments = [
            'CANCEL' => 'Чек сформирован ошибочно',
            'REFUND' => 'Возврат средств'
        ];

        if(!array_key_exists($args->reason, $reasonComments)){
            $this->_abort(
                'Неверно выбрана причина возврата (CANCEL, REFUND)',
                'cancel.invalid_reason');
        }

        if (!$this->error) {
            $payload = [
                'operationTime' => $this->_getUtcTime(),
                'requestTime' => $this->_getUtcTime(),
                'comment' => $reasonComments[$args->reason],
                'receiptUuid' => $args->receiptUuid
            ];

            $this->_makeQuery('cancelReceipt', $payload);
        }
    }

    /**
     * @throws HttpException
     */
    private function makeAuth()
    {
        $args = [
            'username' => $this->username,
            'password' => $this->password,
            'deviceInfo' => [
                'sourceDeviceId' => $this->sourceDeviceId,
                'sourceType' => 'WEB',
                'appVersion' => '1.0.0',
                'metaDetails' => [
                    'serAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.150 Safari/537.36'
                ]
            ]
        ];

        $this->_makeQuery('authLkfl', $args);
    }

    /**
     * @param $method
     * @param $args
     * @return bool|string
     * @throws HttpException
     */
    private function _makeQuery($method, $args)
    {
        $methods = (object) [
            'authLkfl' => (object) [
                'endpoint' => '/auth/lkfl',
                'requiredToken' => false
            ],
            'createReceipt' => (object) [
                'endpoint' => '/income',
                'requiredToken' => true
            ],
            'cancelReceipt' => (object) [
                'endpoint' => '/cancel',
                'requiredToken' => true
            ],
        ];

        $api_url = $this->api_url.$methods->$method->endpoint;
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json, text/plain, */*',
            'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ];

        if ($methods->$method->requiredToken){
            array_push($headers, 'Authorization: Bearer '.$this->token);
        }else{
            $add_params = [
                'username' => $this->username,
                'password' => $this->password,
            ];

            array_push($args, $add_params);
        }

        $payload = json_encode( $args );

        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $api_url);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $out = curl_exec($curl);
            $this->response = $out;
            $json = json_decode($out);

            if ($json){
                if (@$json->exceptionMessage || @$json->code){
                    $this->error = true;

                    $this->errorMessage = @$json->message;
                    $this->errorExceptionMessage = @$json->code ? @$json->code : @$json->exceptionMessage;
                }else{
                    $this->error = false;

                    if ($methods->$method->requiredToken == false){
                        $this->token = @$json->token;
                        $this->inn = @$json->profile->inn;
                    }else{
                        $recUuid = @$json->approvedReceiptUuid ? @$json->approvedReceiptUuid : @$json->incomeInfo->approvedReceiptUuid;

                        $this->receiptUuid = $recUuid;
                        $this->receiptUrlPrint = $this->api_url.'/receipt/'.$this->inn.'/'.$recUuid.'/print';
                        $this->receiptUrlJson = $this->api_url.'/receipt/'.$this->inn.'/'.$recUuid.'/json';
                    }
                }
            }

            curl_close($curl);

            return $out;
        }else {
            throw new HttpException('Can not create connection to ' . $api_url . ' with args ' . $args, 404);
        }


    }
}
