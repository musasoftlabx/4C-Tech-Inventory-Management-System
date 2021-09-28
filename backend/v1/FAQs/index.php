<?php
require '../functions.php';
echo json_encode(ChainPDO("SELECT question, answer FROM FAQs WHERE visible = ?", [1])->fetchAll());
