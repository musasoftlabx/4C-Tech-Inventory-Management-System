<?php
require '../../functions.php';
Tokenify($_GET['token'], false);
echo json_encode(ChainPDO("SELECT PK AS id, code, name FROM sections ORDER BY name")->fetchAll());
