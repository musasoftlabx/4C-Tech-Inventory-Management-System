<?php
require '../functions.php';

echo json_encode([
    'BusinessTypes'        => ChainPDO("SELECT * FROM BusinessTypes")->fetchAll(),
    'BusinessDescriptions' => ChainPDO("SELECT * FROM lnm_business_descriptions")->fetchAll(),
    'BusinessCategories'   => ChainPDO("SELECT category FROM lnm_business_categories")->fetchAll(PDO::FETCH_COLUMN),
    'branches'             => ChainPDO("SELECT * FROM banks")->fetchAll(),
    'banks'                => ChainPDO("SELECT * FROM banks GROUP BY bank")->fetchAll(),
    'counties'             => ChainPDO("SELECT * FROM counties ORDER BY county")->fetchAll(),
    'regions'              => ChainPDO("SELECT * FROM regions ORDER BY region")->fetchAll(),
    'sales_areas'          => ChainPDO("SELECT * FROM sales_areas ORDER BY sales_area")->fetchAll(),
    'clusters'             => ChainPDO("SELECT * FROM clusters ORDER BY cluster")->fetchAll(),
    'nationalities'        => ChainPDO("SELECT nationality FROM nationalities")->fetchAll(PDO::FETCH_COLUMN),
    'TermsAndConditions'   => ChainPDO("SELECT terms FROM terms")->fetch(PDO::FETCH_COLUMN),
    'reissue'              => false,
], JSON_PARTIAL_OUTPUT_ON_ERROR);
