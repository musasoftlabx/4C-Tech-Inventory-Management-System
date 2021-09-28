<?php
require '../../functions.php';
$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
echo json_encode([
    'stores'   => ChainPDO("SELECT code, name FROM stores ORDER BY name")->fetchAll(),
    'sections' => ChainPDO("SELECT code, name FROM sections ORDER BY name")->fetchAll(),
]);
