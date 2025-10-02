<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// Carrega as configurações seguras do banco de dados
$configFile = __DIR__ . '/../../config.php'; // Define o caminho
$config = require $configFile; // Carrega o array de configuração

try {
    // Agora $config é um array e pode ser usado corretamente
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro de conexão com o banco de dados.', 'details' => $e->getMessage()]);
    exit();
}

// Verifica se o método da requisição é GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['message' => 'Método não permitido. Utilize GET.']);
    exit();
}

try {
    // Prepara e executa a query para selecionar todos os integradores
    $stmt = $pdo->prepare("SELECT id, nome_do_integrador, numero_de_contato FROM integradores ORDER BY id DESC");
    $stmt->execute();
    
    // Busca todos os resultados como um array associativo
    $integradores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Retorna o array de integradores como JSON
    http_response_code(200);
    echo json_encode($integradores);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao buscar dados no DB.', 'details' => $e->getMessage()]);
}
?>