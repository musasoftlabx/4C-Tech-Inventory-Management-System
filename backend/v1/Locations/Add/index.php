<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$username = TokenDeconstruct($_JWT, 'username');

$StoreCode   = $_JSON['store']['code'];
$StoreName   = $_JSON['store']['name'];
$SectionCode = $_JSON['section']['code'];
$SectionName = $_JSON['section']['name'];

$CountSimilar = ChainPDO("SELECT COUNT(*) FROM locations WHERE store = ? AND section = ?", [$StoreName, $SectionName])->fetchColumn();

$CountSimilar == 0 ? $CountSimilar = 1 : $CountSimilar++;

$location = 'L' . str_pad($CountSimilar, 4, '0', STR_PAD_LEFT);
$barcode  = $StoreCode . '-' . $SectionCode . '-' . $location;

try
{
    $DB->beginTransaction();

    ChainPDO("INSERT INTO locations VALUES (NULL, ?, ?, ?, 0, 0, 1, ?, now())", [
        $barcode,
        $StoreName,
        $SectionName,
        $username,
    ]);

    $LastID = $DB->lastInsertId();

    $DB->commit();

    echo json_encode(ChainPDO("SELECT *, IF(PhysicalCount > 0, 1, 0) AS edited FROM locations WHERE PK = ?", [$LastID])->fetch());

} catch (PDOException $e) {
    $DB->rollBack();
    http_response_code(500);
    echo SERVER_ERROR;
}
