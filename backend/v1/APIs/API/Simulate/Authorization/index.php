<?php
require '../../../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);

GenerateAccessToken($_JSON['authorization'], true);
