<?php
require '../../../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);

$ShortCode       = $_JSON['ShortCode'];
$ResponseType    = $_JSON['ResponseType'];
$ConfirmationURL = $_JSON['ConfirmationURL'];
$ValidationURL   = $_JSON['ValidationURL'];

$endpoint      = CUSTOMER_TO_BUSINESS_REGISTER_URL_ENDPOINT;
$authorization = GenerateAccessToken($_JSON['authorization'])['access_token'];

$headers = [
    'Authorization: Bearer ' . $authorization,
    'Content-Type: application/json',
];

$request = json_encode([
    'ShortCode'       => $ShortCode,
    'ResponseType'    => $ResponseType,
    'ConfirmationURL' => $ConfirmationURL,
    'ValidationURL'   => $ValidationURL,
]);

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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
        'title'   => 'Service Error!',
        'content' => CURL_ERROR,
    ]);
} else {
    switch ($ResponseCode) {
        case 200:
            $languages = ChainPDO("SELECT language, code FROM APIlanguages WHERE method = ?", ['POST'])->fetchAll(PDO::FETCH_KEY_PAIR);
            $languages = json_encode($languages);
            $languages = str_replace("REPLACE_WITH_ENDPOINT", $endpoint, $languages);
            $languages = str_replace("REPLACE_WITH_AUTHORIZATION", $authorization, $languages);
            $languages = str_replace("REPLACE_WITH_REQUEST_BODY", '{\r\n    \"ShortCode\": ' . $ShortCode . ',\r\n    \"ResponseType\": \"' . $ResponseType . '\",\r\n    \"ConfirmationURL\": \"' . $ConfirmationURL . '\",\r\n    \"ValidationURL\": \"' . $ValidationURL . '\",\r\n  }', $languages);
            $languages = json_decode($languages, true);

            echo json_encode([
                'dataset'   => $response,
                'languages' => $languages,
            ], JSON_PARTIAL_OUTPUT_ON_ERROR);
            break;
        case 400:
        case 404:
        case 500:
            $result = json_decode($response, true);
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
                'title'   => 'Common Exception',
                'content' => $result['errorMessage'],
            ]);
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
                'title'   => 'Unexpected Error!',
                'content' => 'Make sure the information you entered is correct and retry.',
            ]);
            break;
    }
}
