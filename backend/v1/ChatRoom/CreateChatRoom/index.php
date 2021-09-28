<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$username = TokenDeconstruct($_JWT, 'username');
$RoomName = TokenDeconstruct($_JWT, 'FirstName') . ' ' . TokenDeconstruct($_JWT, 'LastName');

$SenderID = ChainPDO("SELECT PK FROM users WHERE username = ?", [$username])->fetchColumn();

$content = strip_tags($_JSON['message']['data'][$_JSON['message']['type']]);

$SenderExists = ChainPDO("SELECT COUNT(*) FROM ChatRooms WHERE SenderID = ?", [$SenderID])->fetchColumn();

if ($SenderExists === 0) {
    ChainPDO("INSERT INTO ChatRooms VALUES (NULL, ?, ?, ?, ?, ?, ?, now(), now(), ?, ?, ?, ?)", [
        $username,
        $RoomName,
        'https://172.29.250.111/img/avatars/1.png',
        0,
        $content,
        $SenderID,
        'true',
        'false',
        'false',
        'true',
    ]);
} else {
    ChainPDO("UPDATE ChatRooms SET LastMessageReceived = ? WHERE SenderID = ?", [$content, $SenderID]);
}

ChainPDO("INSERT INTO ChatMessages VALUES (NULL, ?, ?, ?, now(), now(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
    $username,
    $content,
    $SenderID,
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
