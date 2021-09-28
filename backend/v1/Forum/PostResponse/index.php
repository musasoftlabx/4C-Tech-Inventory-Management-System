<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$FirstName = TokenDeconstruct($_JWT, 'FirstName');
$LastName  = TokenDeconstruct($_JWT, 'LastName');
$username  = TokenDeconstruct($_JWT, 'username');

ChainPDO("INSERT INTO ForumTopicResponses VALUES (NULL, ?, ?, ?, ?, now(), ?)", [
    $_JSON['_'],
    StripUnwantedTagsAndAttrs($_JSON['content']),
    $FirstName . ' ' . $LastName,
    $username,
    $visible = 1,
]);

echo json_encode([
    'color' => GetColorFromLetter($username),
    'dated' => TimeElapsed(date("Y-m-d H:i:s")),
], JSON_PARTIAL_OUTPUT_ON_ERROR);
