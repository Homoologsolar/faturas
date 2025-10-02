<?php
// delete_integrador.php


header('Content-Type: application/json');
$config =  __DIR__ . '../../config.php';

try {
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
// Pega os dados JSON enviados no corpo da requisição
$data = json_decode(file_get_contents("php://input"));
// Valida se o ID foi recebido
if (empty($data->id)) {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'ID do integrador é obrigatório.']); 
    exit();
}
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