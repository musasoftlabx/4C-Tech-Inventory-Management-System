<?php
require '../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$username    = TokenDeconstruct($_JWT, 'username');
$CountryCode = $_JSON['country'];
$StoreCode   = $_JSON['store'];
$SectionCode = $_JSON['section'];

$CountSimilar = ChainPDO("SELECT COUNT(*) FROM locations WHERE CountryCode = ? AND StoreCode = ? AND SectionCode = ?", [$CountryCode, $StoreCode, $SectionCode])->fetchColumn();

$CountSimilar == 0 ? $CountSimilar = 1 : $CountSimilar++;

$location = 'L' . str_pad($CountSimilar, 4, '0', STR_PAD_LEFT);
$barcode  = $CountryCode . '-' . $StoreCode . '-' . $SectionCode . '-' . $location;

try
{
    $DB->beginTransaction();

    ChainPDO("INSERT INTO locations VALUES (NULL, ?, ?, ?, ?, 0, 0, 1, ?, now())", [
        $barcode,
        $CountryCode,
        $StoreCode,
        $SectionCode,
        $username,
    ]);

    $DB->commit();
    echo "Location entry successful.";
} catch (PDOException $e) {
    $DB->rollBack();
    http_response_code(500);
    echo SERVER_ERROR;
}
