<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'test' => 'OK',
    'php_version' => phpversion(),
    'extension_pdo' => extension_loaded('pdo'),
    'datetime' => date('Y-m-d H:i:s'),
    'cwd' => getcwd()
]);
