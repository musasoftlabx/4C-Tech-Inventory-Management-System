<?php
require '../../../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);

$BusinessShortCode = $_JSON['BusinessShortCode'];
$password          = $_JSON['password'];
$timestamp         = $_JSON['timestamp'];
$TransactionType   = $_JSON['TransactionType'];
$amount            = $_JSON['amount'];
$PartyA            = $_JSON['PartyA'];
$PartyB            = $_JSON['PartyB'];
$PhoneNumber       = $_JSON['PhoneNumber'];
$CallBackURL       = $_JSON['CallBackURL'];
$AccountReference  = $_JSON['AccountReference'];
$TransactionDesc   = $_JSON['TransactionDesc'];

$endpoint      = MPESA_EXPRESS_SIMULATE_ENDPOINT;
$authorization = GenerateAccessToken($_JSON['authorization'])['access_token'];

$headers = [
    'Authorization: Bearer ' . $authorization,
    'Content-Type: application/json',
];

$request = json_encode([
    'BusinessShortCode' => $BusinessShortCode,
    'Password'          => $password,
    'Timestamp'         => $timestamp,
    'TransactionType'   => $TransactionType,
    'Amount'            => $amount,
    'PartyA'            => $PartyA,
    'PartyB'            => $PartyB,
    'PhoneNumber'       => $PhoneNumber,
    'CallBackURL'       => $CallBackURL,
    'AccountReference'  => $AccountReference,
    'TransactionDesc'   => $TransactionDesc,
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
            $languages = str_replace("REPLACE_WITH_REQUEST_BODY", '{\r\n    \"BusinessShortCode\": ' . $BusinessShortCode . ',\r\n    \"Password\": \"' . $password . '\",\r\n    \"Timestamp\": \"' . $timestamp . '\",\r\n    \"TransactionType\": \"' . $TransactionType . '\",\r\n    \"Amount\": ' . $amount . ',\r\n    \"PartyA\": ' . $PartyA . ',\r\n    \"PartyB\": ' . $PartyB . ',\r\n    \"PhoneNumber\": ' . $PhoneNumber . ',\r\n    \"CallBackURL\": \"' . $CallBackURL . '\",\r\n    \"AccountReference\": \"' . $AccountReference . '\",\r\n    \"TransactionDesc\": \"' . $TransactionDesc . '\" \r\n  }', $languages);
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
