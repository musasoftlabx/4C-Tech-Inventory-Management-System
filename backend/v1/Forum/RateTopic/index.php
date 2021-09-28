<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$FirstName = TokenDeconstruct($_JWT, 'FirstName');
$LastName  = TokenDeconstruct($_JWT, 'LastName');
$username  = TokenDeconstruct($_JWT, 'username');

$TopicFK = $_JSON['_'];

$AlreadyRated = ChainPDO("SELECT COUNT(RatedBy) FROM ForumTopicRatings WHERE TopicFK = ? AND RatedBy = ?", [$TopicFK, $username])->fetchColumn();

if ($AlreadyRated > 0) {
    http_response_code(403);
    echo json_encode([
        'color'   => 'error',
        'rating'  => ChainPDO("SELECT AVG(rating) FROM ForumTopicRatings WHERE TopicFK = ?", [$TopicFK])->fetchColumn(),
        'content' => 'You have already rated this topic.',
    ]);
} else {
    ChainPDO("INSERT INTO ForumTopicRatings VALUES (NULL, ?, ?, ?, now())", [
        $TopicFK,
        $_JSON['rating'],
        $username,
    ]);

    $AverageRating = ChainPDO("SELECT AVG(rating) FROM ForumTopicRatings WHERE TopicFK = ?", [$TopicFK])->fetchColumn();

    echo json_encode([
        'color'   => 'primary',
        'rating'  => $AverageRating,
        'content' => 'You rated this topic: ' . $_JSON['rating'] . '. Average: ' . $AverageRating,
    ]);
}
