<?php
// Define o tipo de conteúdo da resposta como JSON
header("Content-Type: application/json; charset=UTF-8");
// Permite o acesso de qualquer origem (CORS)
header("Access-Control-Allow-Origin: *");
// Define os métodos HTTP permitidos
header("Access-Control-Allow-Methods: GET, OPTIONS");
// Define os cabeçalhos permitidos
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Responde com sucesso a requisições OPTIONS (pre-flight requests do CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Carrega o arquivo de configuração (caminho corrigido)
$configFile = __DIR__ . '/../config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro crítico: Arquivo de configuração não encontrado.']);
    exit();
}
$config = require $configFile;

// Tenta conectar ao banco de dados
try {
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    // Em produção, é melhor logar o erro do que expô-lo
    echo json_encode(['message' => 'Erro de conexão com o banco de dados.']);
    exit();
}

// Verifica se o método da requisição é GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['message' => 'Método não permitido. Utilize GET.']);
    exit();
}

// Valida se o ID foi passado como parâmetro na URL
if (empty($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'O ID do integrador é obrigatório.']);
    exit();
}

$id = $_GET['id'];

// Prepara e executa a consulta para buscar o integrador pelo ID
try {
    $stmt = $pdo->prepare("SELECT id, nome_do_integrador, numero_de_contato FROM integradores WHERE id = ?");
    $stmt->execute([$id]);
    
    // Busca o resultado como um array associativo
    $integrador = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se o integrador for encontrado, retorna os dados com status 200 (OK)
    if ($integrador) {
        http_response_code(200);
        echo json_encode($integrador);
    } else {
        // Se não for encontrado, retorna erro 404 (Not Found)
        http_response_code(404);
        echo json_encode(['message' => 'Integrador não encontrado.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    // Em produção, logar o erro
    echo json_encode(['message' => 'Erro ao buscar dados no banco de dados.']);
}
?>