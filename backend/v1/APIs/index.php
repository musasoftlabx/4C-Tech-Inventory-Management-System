<?php
require '../functions.php';
echo json_encode(ChainPDO("SELECT name, alias, subsidiaries, subsidiary, parent, color, operation, endpoint, purpose FROM APIs WHERE visible = ?", [1])->fetchAll());
