<?php
require '../functions.php';
echo json_encode(ChainPDO("SELECT TermsAndConditions FROM TermsAndConditions")->fetchColumn());
