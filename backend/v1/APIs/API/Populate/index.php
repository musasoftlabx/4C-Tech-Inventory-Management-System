<?php
require '../../../functions.php';

$API = json_decode(file_get_contents('php://input'), true)['API'];

$operations = ChainPDO("SELECT operation, icon, color, headers FROM APIoperations")->fetchAll();

$dataset = [];

foreach ($operations as $key => $operation) {
    array_push($dataset, [
        'operation' => $operation['operation'],
        'icon'      => $operation['icon'],
        'color'     => $operation['color'],
        'headers'   => $operation['headers'],
        'mapping'   => ChainPDO("SELECT name, description, type, sample FROM APImappings WHERE operationFK = ? AND API = ? AND visible = ?", [$operation['operation'], $API, 1])->fetchAll(),
    ]);
}

echo json_encode([
    'details' => ChainPDO("SELECT name, operation, endpoint, purpose FROM APIs WHERE alias = ? AND visible = ?", [$API, 1])->fetch(),
    'dataset' => $dataset,
]);
