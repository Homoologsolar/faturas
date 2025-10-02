<?php
// Define que a resposta será em formato JSON
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // Permite requisições de qualquer origem (CORS)
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// --- INFORMAÇÕES DO BANCO DE DADOS ---
// IMPORTANTE: Substitua com os dados do banco de dados que você criou na Hostinger
$db_host = 'SEU_DB_HOST';       // Ex: sqlXXX.main-hosting.eu
$db_name = 'SEU_DB_NAME';       // Ex: u123456789_minhadb
$db_user = 'SEU_DB_USER';       // Ex: u123456789_meuuser
$db_pass = 'SUA_DB_SENHA';

// Tenta conectar ao banco de dados
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Se a conexão falhar, retorna um erro 500
    http_response_code(500);
    echo json_encode(['message' => 'Erro de conexão com o banco de dados.', 'details' => $e->getMessage()]);
    exit(); // Encerra o script
}

// Verifica se o método da requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['message' => 'Método não permitido. Utilize POST.']);
    exit();
}

// Pega os dados JSON enviados no corpo da requisição
$data = json_decode(file_get_contents("php://input"));

// Valida se os dados foram recebidos
if (!isset($data->nome_do_integrador) || !isset($data->numero_de_contato)) {
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