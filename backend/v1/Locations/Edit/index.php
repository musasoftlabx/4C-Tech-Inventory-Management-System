<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$username = TokenDeconstruct($_JWT, 'username');
$key      = $_JSON['key'];
$value    = $_JSON['value'];
ChainPDO("UPDATE locations SET PhysicalCount = ?, ModifiedBy = ?, ModifiedOn = now() WHERE PK = ?", [$value, $username, $key]);
