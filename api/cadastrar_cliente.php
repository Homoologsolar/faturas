<?php
// faturas/api/cadastrar_cliente.php
ini_set('display_errors', 1); // Adicione esta linha
error_reporting(E_ALL);     // Adicione esta linha


header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$configFile = __DIR__ . '/../config.php';
$config = require $configFile;

try {
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro de conexão com o banco de dados.']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (empty($data->integrador_id) || empty($data->nome) || empty($data->documento) || empty($data->email) || empty($data->codigo_uc) || empty($data->endereco_instalacao)) {
    http_response_code(400);
    echo json_encode(['message' => 'Todos os campos obrigatórios devem ser preenchidos.']);
    exit();
}

try {
    $pdo->beginTransaction();

    $sqlCliente = "INSERT INTO clientes (nome, documento, email, telefone, endereco_cobranca) VALUES (?, ?, ?, ?, ?)";
    $stmtCliente = $pdo->prepare($sqlCliente);
    $stmtCliente->execute([$data->nome, $data->documento, $data->email, $data->telefone ?? null, $data->endereco_instalacao]);
    $clienteId = $pdo->lastInsertId();

    $sqlInstalacao = "INSERT INTO instalacoes (cliente_id, integrador_id, codigo_uc, endereco_instalacao) VALUES (?, ?, ?, ?)";
    $stmtInstalacao = $pdo->prepare($sqlInstalacao);
    $stmtInstalacao->execute([$clienteId, $data->integrador_id, $data->codigo_uc, $data->endereco_instalacao]);

    $pdo->commit();

    http_response_code(201);
    echo json_encode([
        'message' => 'Cliente e instalação cadastrados com sucesso!',
        'cliente_id' => $clienteId
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    if ($e->getCode() == 23000) {
        echo json_encode(['message' => 'Erro: Documento (CPF/CNPJ) ou Código da UC já existe no sistema.']);
    } else {
        echo json_encode(['message' => 'Erro interno ao cadastrar cliente.', 'details' => $e->getMessage()]);
    }
}
?>