<?php
// faturas/api/gerar_fatura.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- CONFIGURAÇÕES ---
$TARIFA_KWH = 0.90; // Preço do kWh que você vende
$TAXA_DISPONIBILIDADE = 50.00; // Custo mínimo (ex: taxa da distribuidora)

$configFile = __DIR__ . '/../../config.php';
$config = require $configFile;
$pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8", $config['db_user'], $config['db_pass']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents("php://input"));

// Validação dos dados de entrada
if (empty($data->instalacao_id) || !isset($data->consumo_kwh) || !isset($data->injecao_kwh) || empty($data->mes_referencia)) {
    http_response_code(400);
    echo json_encode(['message' => 'Dados incompletos para gerar fatura.']);
    exit();
}

$instalacaoId = $data->instalacao_id;
$consumoKwh = (float)$data->consumo_kwh;
$injecaoKwh = (float)$data->injecao_kwh;
$mesReferencia = $data->mes_referencia . '-01'; // Formato YYYY-MM-DD

try {
    $pdo->beginTransaction();

    // 1. Buscar dados da instalação e do cliente, incluindo saldo de créditos
    $stmt = $pdo->prepare("SELECT i.cliente_id, i.saldo_creditos_kwh, c.id as cliente_id FROM instalacoes i JOIN clientes c ON i.cliente_id = c.id WHERE i.id = ?");
    $stmt->execute([$instalacaoId]);
    $instalacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$instalacao) {
        throw new Exception("Instalação não encontrada.");
    }

    $clienteId = $instalacao['cliente_id'];
    $saldoCreditosAtual = (float)$instalacao['saldo_creditos_kwh'];

    // 2. Gravar a leitura do medidor
    $stmtLeitura = $pdo->prepare("INSERT INTO leituras_medidor (instalacao_id, mes_referencia, consumo_kwh, injecao_kwh, data_leitura) VALUES (?, ?, ?, ?, CURDATE())");
    $stmtLeitura->execute([$instalacaoId, $mesReferencia, $consumoKwh, $injecaoKwh]);

    // 3. Lógica de cálculo da fatura
    $balancoMesKwh = $injecaoKwh - $consumoKwh;
    $creditosUsados = 0;
    $kwhAPagar = 0;
    $novoSaldoCreditos = $saldoCreditosAtual;

    if ($balancoMesKwh >= 0) { // Gerou mais ou igual ao que consumiu
        $novoSaldoCreditos += $balancoMesKwh;
        $valorFatura = $TAXA_DISPONIBILIDADE;
    } else { // Consumiu mais do que gerou
        $kwhFaltantes = abs($balancoMesKwh);
        $creditosUsados = min($kwhFaltantes, $saldoCreditosAtual);
        $novoSaldoCreditos -= $creditosUsados;
        $kwhAPagar = $kwhFaltantes - $creditosUsados;
        $valorFatura = ($kwhAPagar * $TARIFA_KWH) + $TAXA_DISPONIBILIDADE;
    }

    // 4. Inserir a fatura principal
    $dataEmissao = date('Y-m-d');
    $dataVencimento = date('Y-m-d', strtotime('+10 days'));
    $stmtFatura = $pdo->prepare("INSERT INTO faturas (cliente_id, instalacao_id, mes_referencia, data_emissao, data_vencimento, valor_total, status) VALUES (?, ?, ?, ?, ?, ?, 'pendente')");
    $stmtFatura->execute([$clienteId, $instalacaoId, $mesReferencia, $dataEmissao, $dataVencimento, $valorFatura]);
    $faturaId = $pdo->lastInsertId();

    // 5. Inserir os itens da fatura para detalhamento
    $itens = [
        ['descricao' => 'Consumo da Rede (kWh)', 'qtd' => $consumoKwh, 'valor_unit' => $TARIFA_KWH, 'total' => $consumoKwh * $TARIFA_KWH],
        ['descricao' => 'Energia Injetada (kWh)', 'qtd' => $injecaoKwh, 'valor_unit' => $TARIFA_KWH, 'total' => -($injecaoKwh * $TARIFA_KWH)],
        ['descricao' => 'Uso de Créditos Acumulados (kWh)', 'qtd' => $creditosUsados, 'valor_unit' => $TARIFA_KWH, 'total' => -($creditosUsados * $TARIFA_KWH)],
        ['descricao' => 'Taxa de Disponibilidade', 'qtd' => null, 'valor_unit' => null, 'total' => $TAXA_DISPONIBILIDADE]
    ];

    $stmtItem = $pdo->prepare("INSERT INTO fatura_itens (fatura_id, descricao, quantidade_kwh, valor_unitario, valor_total_item) VALUES (?, ?, ?, ?, ?)");
    foreach ($itens as $item) {
        if ($item['total'] != 0) { // Só insere itens com valor
            $stmtItem->execute([$faturaId, $item['descricao'], $item['qtd'], $item['valor_unit'], $item['total']]);
        }
    }

    // 6. Atualizar o saldo de créditos da instalação
    $stmtUpdateCreditos = $pdo->prepare("UPDATE instalacoes SET saldo_creditos_kwh = ? WHERE id = ?");
    $stmtUpdateCreditos->execute([$novoSaldoCreditos, $instalacaoId]);

    $pdo->commit();

    http_response_code(201);
    echo json_encode(['message' => 'Fatura gerada com sucesso!', 'fatura_id' => $faturaId]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao gerar fatura.', 'details' => $e->getMessage()]);
}
?>