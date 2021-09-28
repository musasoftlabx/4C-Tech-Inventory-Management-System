<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);

ChainPDO("INSERT INTO ChatMessages VALUES (NULL, ?, ?, ?, now(), now(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
    $_JSON['ReplyingTo'],
    strip_tags($_JSON['content']),
    $_JSON['SenderID'],
    'false',
    'true',
    'false',
    'false',
    'false',
    'false',
    null,
    null,
    null,
    null,
    null,
    null,
]);
