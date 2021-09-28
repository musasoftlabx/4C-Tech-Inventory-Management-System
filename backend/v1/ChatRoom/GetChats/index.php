<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);

$ChatRooms = ChainPDO("SELECT * FROM ChatRooms ORDER BY PK DESC")->fetchAll();

$chats = [];

foreach ($ChatRooms as $key => $ChatRoom) {
    $PK = $ChatRoom['PK'];
    array_push($chats, [
        '_'           => $PK,
        'roomId'      => $ChatRoom['username'],
        'roomName'    => $ChatRoom['RoomName'],
        'avatar'      => $ChatRoom['avatar'],
        'unreadCount' => $ChatRoom['UnreadCount'],
        'lastMessage' => [
            'content'     => $ChatRoom['LastMessageReceived'],
            'sender_id'   => $ChatRoom['SenderID'],
            'username'    => $ChatRoom['username'],
            'timestamp'   => $ChatRoom['timestamp'],
            'date'        => $ChatRoom['date'],
            'saved'       => $ChatRoom['saved'] === 'true' ? true : false,
            'distributed' => $ChatRoom['distributed'] === 'true' ? true : false,
            'seen'        => $ChatRoom['seen'] === 'true' ? true : false,
            'new'         => $ChatRoom['new'] === 'true' ? true : false,
        ],
        'users'       => [],
    ]);
}

echo json_encode($chats);
