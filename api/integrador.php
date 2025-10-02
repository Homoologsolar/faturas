<?php
// Define que a resposta será em formato JSON
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // Permite requisições de qualquer origem (CORS)
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// O navegador envia uma requisição OPTIONS (pre-flight) para verificar o CORS.
// É importante responder a ela com sucesso para que a requisição POST seja enviada.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- CARREGA AS CONFIGURAÇÕES SEGURAS ---
// O caminho '../..' sobe dois níveis de diretório (de /api/ para /public_html/ e depois para a raiz)
// para encontrar o arquivo config.php

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

if (!file_exists($configFile)) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro crítico: Arquivo de configuração não encontrado.']);
    exit();
}

$config = require $configFile;

$db_host = $config['db_host'];
$db_name = $config['db_name'];
$db_user = $config['db_user'];
$db_pass = $config['db_pass'];

// Tenta conectar ao banco de dados
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Se a conexão falhar, retorna um erro 500
    http_response_code(500);
    echo json_encode(['message' => 'Erro de conexão com o banco de dados.', 'details' => $e->getMessage()]);
    exit();
}

// Verifica se o método da requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['message' => 'Método não permitido. Utilize POST.']);
    exit();
}

// Pega os dados JSON enviados no corpo da requisição
$data = json_decode(file_get_contents("php://input"));

// Valida se os dados foram recebidos e não estão vazios
if (empty($data->nome_do_integrador) || empty($data->numero_de_contato)) {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'Nome e numero de contato são obrigatórios.']);
    exit();
}

// Prepara a query SQL para evitar SQL Injection
$sql = "INSERT INTO integradores (nome_do_integrador, numero_de_contato) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);

// Executa a query com os dados recebidos
try {
    $stmt->execute([$data->nome_do_integrador, $data->numero_de_contato]);
    $lastId = $pdo->lastInsertId();

    // Se tudo deu certo, retorna uma resposta de sucesso
    http_response_code(201); // Created
    echo json_encode([
        'id' => $lastId,
        'message' => 'Registro Integrador inserido com sucesso!'
    ]);

} catch (PDOException $e) {
    // Se a inserção falhar, retorna um erro 500
    http_response_code(500);
    echo json_encode(['message' => 'Erro interno ao inserir dados no DB.', 'details' => $e->getMessage()]);
}
?>