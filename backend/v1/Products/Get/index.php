<?php
require '../../functions.php';
Tokenify($_GET['token'], false);

$page  = $_GET['page'];
$limit = $_GET['limit'];
$start = $page * $limit;

$filter = '';

$CountQuery = "SELECT COUNT(PK) FROM products";

if (isset($_GET['filter'])) {
    $filter .= ' WHERE ';
    $_filter = urldecode($_GET['filter']);
    $_filter = json_decode($_filter, true)[0];
    //print_r($_filter);die();

    $property = $_filter['property'];
    $value    = $_filter['value'];
    $operator = strtoupper($_filter['operator']);

    if ($operator === 'LIKE') {
        $filter .= "`" . $property . "` " . $operator . " '%" . $value . "%'";
    } else {
        $filter .= "`" . $property . "` " . $operator . " '" . $value . "'";
    }

    /*for ($i = 0; $ioperator) {
    $value    = $filterItem->value;
    $property = $filterItem->property;

    if ($i !== count($_filter) - 1) {
    $filter .= 'AND';
    }
    }*/
    $count = $CountQuery . $filter;
} else {
    $count = $CountQuery;
}

function getOperator($operator)
{
    switch ($operator) {
        case 'lt':
            return '<';
            break;
        case 'gt':
            return '>';
            break;
        case '<=':
            return 'lteq';
            break;
        case '>=':
            return 'gteq';
            break;
        case 'eq':
        case 'stricteq':
            return '=';
            break;
            break;
        case 'noteq':
        case 'notstricteq':
            return '!=';
            break;
        case 'like':
            return 'LIKE';
            break;
    }
}

echo json_encode([
    'data'       => ChainPDO("SELECT *, PK AS id FROM products $filter LIMIT $start, $limit")->fetchAll(),
    'totalCount' => ChainPDO($count)->fetch(PDO::FETCH_COLUMN),
]);
