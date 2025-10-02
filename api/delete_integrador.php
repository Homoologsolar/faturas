<?php
// delete_integrador.php


header('Content-Type: application/json');
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
// Verifica se o método da requisição é DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['message' => 'Método não permitido. Utilize DELETE.']);
    exit();
}
// Valida se o ID foi recebido via URL
if (empty($_GET['id'])) { // Lê o ID da URL
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'ID do integrador é obrigatório.']); 
    exit();
}

// Pega o ID
$id = $_GET['id'];

// Prepara a query SQL para evitar SQL Injection
$sql = "DELETE FROM integradores WHERE id = ?";
$stmt = $pdo->prepare($sql);
// Executa a query com o ID recebido
try {
    $stmt->execute([$data->id]);
    if ($stmt->rowCount() > 0) {
        http_response_code(200); // OK
        echo json_encode(['message' => 'Integrador deletado com sucesso!']);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(['message' => 'Integrador não encontrado.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao deletar integrador no DB.', 'details' => $e->getMessage()]);
}
?>
<?php
// integrador.php