<?php
// delete_integrador.php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- CORREÇÃO 1: Carregar o arquivo de configuração ---
$configFile = __DIR__ . '/../../config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro crítico: Arquivo de configuração não encontrado.']);
    exit();
}
$config = require $configFile;

try {
    // Agora a conexão usará o array $config corretamente
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro de conexão com o banco de dados.', 'details' => $e->getMessage()]);
    exit();
}   

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['message' => 'Método não permitido. Utilize DELETE.']);
    exit();
}

// --- CORREÇÃO 2: Ler o ID da URL com $_GET ---
if (empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['message' => 'ID do integrador é obrigatório.']); 
    exit();
}

$id = $_GET['id'];

$sql = "DELETE FROM integradores WHERE id = ?";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([$id]); // Usar a variável $id
    
    if ($stmt->rowCount() > 0) {
        http_response_code(200);
        echo json_encode(['message' => 'Integrador deletado com sucesso!']);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Integrador não encontrado.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao deletar integrador no DB.', 'details' => $e->getMessage()]);
}
// --- CORREÇÃO 3: Remover a chave '}' extra que existia aqui ---
?>