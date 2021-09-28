<?php
require '../../functions.php';
Tokenify(json_decode(file_get_contents('php://input'), true)['token'], false);
echo json_encode(ChainPDO("SELECT *, IF(PhysicalCount > 0, 1, 0) AS edited FROM locations ORDER BY PK DESC")->fetchAll());
