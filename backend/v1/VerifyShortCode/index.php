<?php
require '../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$EmailAddress = TokenDeconstruct($_JWT, 'EmailAddress');
$PhoneNumber  = str_replace(['+', ' '], '', TokenDeconstruct($_JWT, 'PhoneNumber'));

$MpesaUsername         = trim($_JSON['MpesaUsername']);
$OrganizationShortCode = $_JSON['OrganizationShortCode'];

$request =
    '{
        "username":"' . $MpesaUsername . '",
        "shortcode":"' . $OrganizationShortCode . '",
        "secret":"Ap!g33One27"
    }';

$endpoint = SHORTCODE_VERIFICATION_ENDPOINT;
$ch       = curl_init($endpoint);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode(APIGEE_USERNAME . ':' . APIGEE_PASSWORD),
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, CURL_TIMEOUT);
$response     = curl_exec($ch);
$error        = curl_error($ch);
$ResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$ResponseTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
http_response_code($ResponseCode);

if ($error) {
    LogToDB(json_encode(
        [
            'logtype'      => 'EMERGENCY',
            'operation'    => 'API',
            'ResponseCode' => $ResponseCode,
            'ResponseTime' => $ResponseTime,
            'service'      => basename(dirname(__FILE__)),
            'endpoint'     => $endpoint,
            'request'      => $request,
            'response'     => $error,
        ]
    ));

    echo json_encode([
        'title'   => 'Service error!',
        'content' => CURL_ERROR,
    ]);
} else {
    switch ($ResponseCode) {
        case 200:
            #Generate OTP to phone number
            $MSISDN = json_decode($response, true)['MSISDN'];
            if (substr($MSISDN, 0, 1) == 0) {
                $MSISDN = 254 . ltrim($MSISDN, 0);
            }
            echo $MSISDN;
            $endpoint = GENERATE_OTP_ENDPOINT . $MSISDN;
            $ch2      = curl_init($endpoint);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
            $response     = curl_exec($ch2);
            $error        = curl_error($ch2);
            $ResponseCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
            $ResponseTime = curl_getinfo($ch2, CURLINFO_TOTAL_TIME);
            curl_close($ch2);
            break;
        default:
            LogToDB(json_encode(
                [
                    'logtype'      => 'ERROR',
                    'operation'    => 'API',
                    'ResponseCode' => $ResponseCode,
                    'ResponseTime' => $ResponseTime,
                    'service'      => basename(dirname(__FILE__)),
                    'endpoint'     => $endpoint,
                    'request'      => $request,
                    'response'     => $response,
                ]
            ));

            echo json_encode([
                'title'   => 'Verification failed!',
                'content' => 'Paybill or M-Pesa username keyed in does not exist.',
            ]);
            break;
    }
}

curl_close($ch);
