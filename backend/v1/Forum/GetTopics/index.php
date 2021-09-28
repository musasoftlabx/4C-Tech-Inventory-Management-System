<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);

$limit  = $_JSON['TopicsPerPage'];
$offset = $_JSON['page'];
$order  = $_JSON['order'];

function GetResponses($TopicFK)
{
    $ForumTopicResponses = ChainPDO("SELECT * FROM ForumTopicResponses WHERE TopicFK = ? AND visible = ? ORDER BY PK DESC", [$TopicFK, 1])->fetchAll();

    $responses = [];

    foreach ($ForumTopicResponses as $key => $ForumTopicResponse) {
        array_push($responses, [
            '_'        => $ForumTopicResponse['PK'],
            'color'    => GetColorFromLetter($ForumTopicResponse['PostedBy']),
            'content'  => $ForumTopicResponse['content'],
            'PostedBy' => $ForumTopicResponse['PostedBy'],
            'dated'    => TimeElapsed(date($ForumTopicResponse['PostedOn'])),
        ]);
    }

    return $responses;
}

switch ($order) {
    case 'Oldest':
        $ForumTopics = ChainPDO("SELECT * FROM ForumTopics WHERE visible = ? ORDER BY PK LIMIT ?, ?", [1, $offset, $limit])->fetchAll();
        break;
    case 'Relevance':
        $ForumTopics = ChainPDO("SELECT ForumTopics.PK, topic, content, username, PostedBy, PostedOn
            FROM ForumTopics
            WHERE visible = ?
            ORDER BY (SELECT AVG(rating) FROM ForumTopicRatings WHERE TopicFK = ForumTopics.PK) DESC
            LIMIT ?, ?", [1, $offset, $limit]
        )->fetchAll();
        break;
    default:
        $ForumTopics = ChainPDO("SELECT * FROM ForumTopics WHERE visible = ? ORDER BY PK DESC LIMIT ?, ?", [1, $offset, $limit])->fetchAll();
        break;
}

$topics = [];

foreach ($ForumTopics as $key => $ForumTopic) {
    $PK = $ForumTopic['PK'];
    array_push($topics, [
        '_'        => $PK,
        'color'    => GetColorFromLetter($ForumTopic['PostedBy']),
        'topic'    => $ForumTopic['topic'],
        'rating'   => ChainPDO("SELECT AVG(rating) FROM ForumTopicRatings WHERE TopicFK = ?", [$PK])->fetchColumn(),
        'content'  => $ForumTopic['content'],
        'PostedBy' => $ForumTopic['PostedBy'],
        'username' => $ForumTopic['username'],
        'dated'    => TimeElapsed(date($ForumTopic['PostedOn'])),
        'response' => [
            'visible'   => false,
            'loading'   => false,
            'content'   => '',
            'responses' => GetResponses($PK),
        ],

    ]);
}

echo json_encode($topics);
