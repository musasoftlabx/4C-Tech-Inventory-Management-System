<?php
require '../functions.php';
echo json_encode(ChainPDO("SELECT InitiatorName, InitiatorPassword, PartyA, PartyB, PhoneNumber, BusinessShortCode, PassKey FROM TestCredentials ORDER BY RAND()")->fetch());
