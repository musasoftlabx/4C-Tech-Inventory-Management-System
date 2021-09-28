<?php
require '../../functions.php';

$_JSON = json_decode(file_get_contents('php://input'), true);

$dataset = ChainPDO("SELECT PK AS id, code, name, country, ModifiedBy, ModifiedOn FROM stores ORDER BY PK DESC")->fetchAll();

foreach ($dataset as $key => $data) {
    $dataset[$key]['ModifiedOn'] = date('D, jS \of M Y \a\t H:i:s a', strtotime($data['ModifiedOn']));
}

echo json_encode($dataset);
