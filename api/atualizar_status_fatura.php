<?php
// faturas/api/atualizar_status_fatura.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$configFile = __DIR__ . '/../config.php';
$config = require $configFile;
$pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents("php://input"));

if (empty($data->fatura_id) || empty($data->status)) {
    http_response_code(400);
    echo json_encode(['message' => 'ID da fatura e novo status são obrigatórios.']);
    exit();
}

$allowed_statuses = ['pendente', 'paga', 'vencida', 'cancelada'];
if (!in_array($data->status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['message' => 'Status inválido.']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE faturas SET status = ? WHERE id = ?");
    $stmt->execute([$data->status, $data->fatura_id]);

    http_response_code(200);
    echo json_encode(['message' => 'Status da fatura atualizado com sucesso!']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao atualizar o status.', 'details' => $e->getMessage()]);
}
?>