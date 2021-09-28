<?php
require '../functions.php';
$page = file_get_contents('php://input');

ChainPDO("INSERT INTO metrics VALUES (NULL, ?, ?, ?, ?, ?, now())", [
    GetClientIP(),
    GetDevice(),
    GetOS(),
    GetBrowser(),
    $page,
]);
