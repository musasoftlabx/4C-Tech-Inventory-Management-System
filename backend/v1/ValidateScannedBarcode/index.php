<?php
require '../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$username        = TokenDeconstruct($_JWT, 'username');
$LocationPK      = $_JSON['LocationPK'];
$LocationBarcode = $_JSON['LocationBarcode'];
$ProductBarcode  = $_JSON['ProductBarcode'];

$DoesExist = ChainPDO("SELECT COUNT(*) FROM products WHERE TRIM(barcode) = ?", [$ProductBarcode])->fetchColumn();

if ($DoesExist > 0) {
    ChainPDO("UPDATE products SET LocationFK = ? WHERE barcode = ?", [$LocationPK, $ProductBarcode]);
    ChainPDO("UPDATE locations SET SystemCount = SystemCount + 1 WHERE barcode = ?", [$LocationBarcode]);
    ChainPDO("INSERT INTO scans VALUES (NULL, ?, ?, ?, now())", [
        $LocationBarcode,
        $ProductBarcode,
        $username,
    ]);
    echo 'Product was found and updated';
} else {
    http_response_code(409);
    echo 'This item does not exist in our records.';
}
