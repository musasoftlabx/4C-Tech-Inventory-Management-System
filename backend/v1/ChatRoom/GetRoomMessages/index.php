<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);

$SingleRoom = $_JSON['SingleRoom'];

$messages = [];

if ($SingleRoom) {
    $_JWT = $_JSON['token'];
    Tokenify($_JWT, false);
    $username = TokenDeconstruct($_JWT, 'username');

    $ChatMessages = ChainPDO("SELECT * FROM ChatMessages WHERE username = ?", [$username])->fetchAll();

    foreach ($ChatMessages as $key => $ChatMessage) {
        array_push($messages, [
            '_id'         => $ChatMessage['PK'],
            'type'        => 'text',
            'author'      => $ChatMessage['SenderID'] !== 0 ? 'me' : $ChatMessage['username'],
            'data'        => [
                'text' => $ChatMessage['content'],
            ],
            'suggestions' => [],
        ]);
    }
} else {
    $username = $_JSON['username'];

    $ChatMessages = ChainPDO("SELECT * FROM ChatMessages WHERE username = ?", [$username])->fetchAll();

    foreach ($ChatMessages as $key => $ChatMessage) {
        array_push($messages, [
            '_id'               => $ChatMessage['PK'],
            'content'           => $ChatMessage['content'],
            'sender_id'         => $ChatMessage['SenderID'],
            'username'          => $ChatMessage['username'],
            'timestamp'         => $ChatMessage['timestamp'],
            'date'              => $ChatMessage['date'],
            'system'            => $ChatMessage['system'] === 'true' ? true : false,
            'saved'             => $ChatMessage['saved'] === 'true' ? true : false,
            'distributed'       => $ChatMessage['distributed'] === 'true' ? true : false,
            'seen'              => $ChatMessage['seen'] === 'true' ? true : false,
            'disable_actions'   => $ChatMessage['DisableActions'],
            'disable_reactions' => $ChatMessage['DisableReactions'],
            'FileName'          => $ChatMessage['FileName'],
            'FileSize'          => $ChatMessage['FileSize'],
            'FileType'          => $ChatMessage['FileType'],
            'FileAudio'         => $ChatMessage['FileAudio'],
            'FileDuration'      => $ChatMessage['FileDuration'],
            'FileURL'           => $ChatMessage['FileURL'],
        ]);
    }
}

echo json_encode($messages);
