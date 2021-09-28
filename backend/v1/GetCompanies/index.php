<?php
require '../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$EmailAddress = TokenDeconstruct($_JWT, 'EmailAddress');

$url = GET_COMPANIES_ENDPOINT . $EmailAddress;
$ch  = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . APIGEE_AUTHORIZATION,
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response     = curl_exec($ch);
$error        = curl_error($ch);
$ResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
http_response_code($ResponseCode);

if ($error) {
    echo SERVER_ERROR;
} else {
    $result = json_decode($response, true);
    switch ($ResponseCode) {
        case 200:
        case 201:
            $dataset = [];

            foreach ($result['companies'] as $key => $company) {
                array_push($dataset, $company);
            }

            echo json_encode($dataset);
            break;
        case 400:
        case 401:
        case 403:
        case 404:
            echo $result['message'];
            break;
        default:
            echo SERVER_ERROR;
            break;
    }
}

curl_close($ch);
