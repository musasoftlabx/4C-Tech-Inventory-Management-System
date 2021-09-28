<?php
require '../../functions.php';

echo json_encode([
    'count'           => ChainPDO("SELECT COUNT(PK) FROM ForumTopics WHERE visible = ? ORDER BY PK DESC", [1])->fetchColumn(),
    'TopContributors' => ChainPDO("SELECT username, PostedBy, COUNT(username) AS count FROM ForumTopics WHERE visible = ? GROUP BY username, PostedBy ORDER BY count DESC LIMIT 4", [1])->fetchAll(),
]);
