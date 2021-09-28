<?php
require '../../../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);

$initiator              = $_JSON['initiator'];
$SecurityCredential     = $_JSON['SecurityCredential'];
$CommandID              = $_JSON['CommandID'];
$TransactionID          = $_JSON['TransactionID'];
$amount                 = $_JSON['amount'];
$ReceiverParty          = $_JSON['ReceiverParty'];
$ReceiverIdentifierType = $_JSON['ReceiverIdentifierType'];
$ResultURL              = $_JSON['ResultURL'];
$QueueTimeOutURL        = $_JSON['QueueTimeOutURL'];
$remarks                = $_JSON['remarks'];
$occassion              = $_JSON['occassion'];

$endpoint      = REVERSAL_ENDPOINT;
$authorization = GenerateAccessToken($_JSON['authorization'])['access_token'];

$headers = [
    'Authorization: Bearer ' . $authorization,
    'Content-Type: application/json',
];

$request = json_encode([
    'Initiator'              => $initiator,
    'SecurityCredential'     => $SecurityCredential,
    'CommandID'              => $CommandID,
    'TransactionID'          => $TransactionID,
    'amount'                 => $amount,
    'ReceiverParty'          => $ReceiverParty,
    'ReceiverIdentifierType' => $ReceiverIdentifierType,
    'ResultURL'              => $ResultURL,
    'QueueTimeOutURL'        => $QueueTimeOutURL,
    'Remarks'                => $remarks,
    'Occassion'              => $occassion,
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
            $languages = str_replace("REPLACE_WITH_REQUEST_BODY", '{\r\n    \"initiator\": \"' . $initiator . '\",\r\n    \"SecurityCredential\": \"' . $SecurityCredential . '\",\r\n    \"CommandID\": \"' . $CommandID . '\",\r\n    \"TransactionID\": ' . $TransactionID . ',\r\n    \"amount\": ' . $amount . ',\r\n    \"ReceiverParty\": \"' . $ReceiverParty . '\",\r\n    \"ReceiverIdentifierType\": \"' . $ReceiverIdentifierType . '\",\r\n    \"ResultURL\": \"' . $ResultURL . '\",\r\n    \"QueueTimeOutURL\": \"' . $QueueTimeOutURL . '\",\r\n    \"remarks\": \"' . $remarks . '\",\r\n    \"occassion\": \"' . $occassion . '\",\r\n  }', $languages);
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
