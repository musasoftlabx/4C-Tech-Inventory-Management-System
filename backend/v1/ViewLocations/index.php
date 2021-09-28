<?php
require '../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$username = TokenDeconstruct($_JWT, 'username');

$locations = ChainPDO("SELECT * FROM locations")->fetchAll();

echo json_encode($locations);
