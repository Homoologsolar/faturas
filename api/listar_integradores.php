<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$configFile = __DIR__ . '/../../config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro crítico: Arquivo de configuração não encontrado.']);
    exit();
}
$config = require $configFile;

try {
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro de conexão com o banco de dados.', 'details' => $e->getMessage()]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['message' => 'Método não permitido. Utilize GET.']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id, nome_do_integrador, numero_de_contato FROM integradores ORDER BY id DESC");
    $stmt->execute();
    $integradores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    http_response_code(200);
    echo json_encode($integradores);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao buscar dados no DB.', 'details' => $e->getMessage()]);
}
?>