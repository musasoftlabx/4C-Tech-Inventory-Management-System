<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);
$_JWT  = $_JSON['token'];
Tokenify($_JWT, false);
$FirstName = TokenDeconstruct($_JWT, 'FirstName');
$LastName  = TokenDeconstruct($_JWT, 'LastName');
$username  = TokenDeconstruct($_JWT, 'username');

$ProposedTopic = strip_tags($_JSON['topic']);

$topics = ChainPDO("SELECT topic FROM ForumTopics")->fetchAll(PDO::FETCH_COLUMN);

# Count if other topics exists to compare to prevent error
if (count($topics) > 0) {
    $similarities = [];

    foreach ($topics as $key => $topic) {
        $similarity = similar_text($ProposedTopic, $topic, $percentage);
        array_push($similarities, [
            'topic'      => $topic,
            'percentage' => $percentage,
        ]);
    }

    usort($similarities, function ($a, $b) {
        return $b['percentage'] - $a['percentage'];
    });

    $SimilarTopic           = $similarities[0]['topic'];
    $SimilarTopicPercentage = $similarities[0]['percentage'];

    if ($SimilarTopicPercentage > 80) {
        http_response_code(403);
        echo json_encode([
            'topic'   => $SimilarTopic,
            'title'   => 'Similar topic',
            'content' => 'A similar topic named <b>' . $SimilarTopic . '</b>  already exists. Check it out on the wall or re-phrase your topic and try again.',
        ]);
    } else {
        ChainPDO("INSERT INTO ForumTopics VALUES (NULL, ?, ?, ?, ?, now(), ?)", [
            ucfirst($_JSON['topic']),
            StripUnwantedTagsAndAttrs($_JSON['content']),
            $FirstName . ' ' . $LastName,
            $username,
            $visible = 1,
        ]);

        echo json_encode([
            'color' => GetColorFromLetter($username),
            'dated' => TimeElapsed(date("Y-m-d H:i:s")),
        ], JSON_PARTIAL_OUTPUT_ON_ERROR);
    }
} else {
    ChainPDO("INSERT INTO ForumTopics VALUES (NULL, ?, ?, ?, ?, now(), ?)", [
        ucfirst($_JSON['topic']),
        StripUnwantedTagsAndAttrs($_JSON['content']),
        $FirstName . ' ' . $LastName,
        $username,
        $visible = 1,
    ]);

    echo json_encode([
        'color' => GetColorFromLetter($username),
        'dated' => TimeElapsed(date("Y-m-d H:i:s")),
    ], JSON_PARTIAL_OUTPUT_ON_ERROR);
}
