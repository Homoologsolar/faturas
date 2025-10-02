<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// Carrega as configurações seguras do banco de dados

$path_producao = __DIR__ . '/../../config.php';
$path_local = __DIR__ . '/../config.php';

if (file_exists($path_producao)) {
    // Se encontrou o arquivo na estrutura da Hostinger (produção)
    $config = require $path_producao;
} elseif (file_exists($path_local)) {
    // Se encontrou o arquivo na estrutura local (desenvolvimento)
    $config = require $path_local;
} else {
    // Se não encontrou nenhum, retorna um erro crítico
    http_response_code(500);
    echo json_encode(['message' => 'Erro crítico: Arquivo de configuração não encontrado em nenhum ambiente.']);
    exit();
}

// O restante do seu código continua exatamente o mesmo...
$db_host = $config['db_host'];

try {
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