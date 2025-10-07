<?php
// faturas/api/get_faturas.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

$configFile = __DIR__ . '/../config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro: Arquivo de configuração não encontrado.']);
    exit();
}
$config = require $configFile;

try {
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro de conexão com o banco de dados.']);
    exit();
}

if (empty($_GET['cliente_id'])) {
    http_response_code(400);
    echo json_encode(['message' => 'O ID do cliente é obrigatório.']);
    exit();
}

$clienteId = $_GET['cliente_id'];
$response = [];

try {
    // 1. Buscar informações do cliente
    $stmtCliente = $pdo->prepare("SELECT id, nome, documento FROM clientes WHERE id = ?");
    $stmtCliente->execute([$clienteId]);
    $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        http_response_code(404);
        echo json_encode(['message' => 'Cliente não encontrado.']);
        exit();
    }
    $response['cliente'] = $cliente;

    // 2. Buscar a lista de faturas do cliente
    $stmtFaturas = $pdo->prepare(
        "SELECT id, mes_referencia, data_vencimento, valor_total, status 
         FROM faturas 
         WHERE cliente_id = ? 
         ORDER BY mes_referencia DESC"
    );
    $stmtFaturas->execute([$clienteId]);
    $faturas = $stmtFaturas->fetchAll(PDO::FETCH_ASSOC);

    $response['faturas'] = $faturas;

    http_response_code(200);
    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao buscar dados no banco de dados.', 'details' => $e->getMessage()]);
}
?>