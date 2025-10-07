<?php
// faturas/api/get_clientes_por_integrador.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

$configFile = __DIR__ . '/../../config.php';
$config = require $configFile;

try {
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro de conexão com o banco de dados.']);
    exit();
}

if (empty($_GET['integrador_id'])) {
    http_response_code(400);
    echo json_encode(['message' => 'O ID do integrador é obrigatório.']);
    exit();
}

$integradorId = $_GET['integrador_id'];

try {
    // Consulta que junta as tabelas clientes e instalacoes
    $sql = "SELECT 
                c.id, 
                c.nome, 
                i.codigo_uc 
            FROM clientes c
            JOIN instalacoes i ON c.id = i.cliente_id
            WHERE i.integrador_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$integradorId]);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($clientes);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao buscar clientes.', 'details' => $e->getMessage()]);
}
?>