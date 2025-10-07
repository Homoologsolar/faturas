<?php
// faturas/api/listar_instalacoes.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

$configFile = __DIR__ . '/../config.php';
$config = require $configFile;
$pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $stmt = $pdo->prepare("
        SELECT i.id, i.codigo_uc, c.nome 
        FROM instalacoes i 
        JOIN clientes c ON i.cliente_id = c.id
        ORDER BY c.nome ASC
    ");
    $stmt->execute();
    $instalacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($instalacoes);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao listar instalações.']);
}
?>