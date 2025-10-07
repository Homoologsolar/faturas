<?php
// faturas/api/get_detalhes_fatura.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

$configFile = __DIR__ . '/../config.php';
$config = require $configFile;
$pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (empty($_GET['fatura_id'])) {
    http_response_code(400);
    echo json_encode(['message' => 'O ID da fatura é obrigatório.']);
    exit();
}

$faturaId = $_GET['fatura_id'];
$response = [];

try {
    // Buscar dados da fatura, cliente e instalação
    $stmt = $pdo->prepare("
        SELECT 
            f.id, f.mes_referencia, f.data_emissao, f.data_vencimento, f.valor_total, f.status,
            c.nome as cliente_nome, c.documento as cliente_documento,
            i.codigo_uc, i.endereco_instalacao
        FROM faturas f
        JOIN clientes c ON f.cliente_id = c.id
        JOIN instalacoes i ON f.instalacao_id = i.id
        WHERE f.id = ?
    ");
    $stmt->execute([$faturaId]);
    $fatura = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fatura) {
        http_response_code(404);
        echo json_encode(['message' => 'Fatura não encontrada.']);
        exit();
    }
    $response['fatura'] = $fatura;

    // Buscar itens da fatura
    $stmtItens = $pdo->prepare("SELECT * FROM fatura_itens WHERE fatura_id = ? ORDER BY id");
    $stmtItens->execute([$faturaId]);
    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);
    $response['itens'] = $itens;

    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao buscar detalhes da fatura.', 'details' => $e->getMessage()]);
}
?>