
<?php

header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/config.php';

// 1. Permite somente POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

// 2. Verifica a chave de API
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

if (!isset($env['API_KEY']) || $apiKey !== $env['API_KEY']) {
    http_response_code(401);
    echo json_encode(['erro' => 'Chave de API inválida']);
    exit;
}

// 3. Lê e valida o JSON
$body = file_get_contents('php://input');
$dados = json_decode($body, true);

if (!is_array($dados)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Body inválido']);
    exit;
}

// 4. Valida a matrícula
$matricula = (string) ($dados['matricula'] ?? '');

if (!preg_match('/^\d{11}$/', $matricula)) {
    http_response_code(400);
    echo json_encode([
        'erro' => 'Matrícula deve possuir 11 dígitos'
    ]);
    exit;
}

// 5. Valida o operador
if (!isset($dados['operador']) || !is_string($dados['operador'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Operador inválido']);
    exit;
}

$operador = trim($dados['operador']);

if ($operador === '' || strlen($operador) > 100) {
    http_response_code(400);
    echo json_encode([
        'erro' => 'Operador deve possuir entre 1 e 100 caracteres'
    ]);
    exit;
}

// 6. Valida o valor da recarga
if (
    !isset($dados['valor']) ||
    (!is_string($dados['valor']) &&
     !is_int($dados['valor']) &&
     !is_float($dados['valor']))
) {
    http_response_code(400);
    echo json_encode(['erro' => 'Valor da recarga inválido']);
    exit;
}

$valorTexto = (string) $dados['valor'];

if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $valorTexto)) {
    http_response_code(400);
    echo json_encode([
        'erro' => 'Valor deve ser positivo e ter no máximo duas casas decimais'
    ]);
    exit;
}

// Normaliza o valor para duas casas decimais
$partes = explode('.', $valorTexto);
$inteiro = ltrim($partes[0], '0');
$inteiro = ($inteiro === '') ? '0' : $inteiro;
$centavos = str_pad($partes[1] ?? '', 2, '0');

$valor = $inteiro . '.' . $centavos;

if ($valor === '0.00') {
    http_response_code(400);
    echo json_encode([
        'erro' => 'Valor da recarga deve ser maior que zero'
    ]);
    exit;
}

// 7. Inicia a transação
try {
    $pdo->beginTransaction();

    // 8. Consulta e bloqueia a linha do saldo
    $sqlAnterior = "
        SELECT saldo
        FROM saldos
        WHERE matricula = :matricula
        FOR UPDATE
    ";

    $stmtAnterior = $pdo->prepare($sqlAnterior);

    $stmtAnterior->execute([
        ':matricula' => $matricula
    ]);

    $registroAnterior = $stmtAnterior->fetch(PDO::FETCH_ASSOC);

    if ($registroAnterior === false) {
        $pdo->rollBack();

        http_response_code(404);
        echo json_encode([
            'erro' => 'Matrícula não encontrada na tabela de saldos'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $saldoAnterior = $registroAnterior['saldo'];

    // 9. Atualiza o saldo
    $sqlAtualiza = "
        UPDATE saldos
        SET saldo = saldo + :valor,
            atualizado_em = NOW()
        WHERE matricula = :matricula
    ";

    $stmtAtualiza = $pdo->prepare($sqlAtualiza);

    $stmtAtualiza->execute([
        ':valor' => $valor,
        ':matricula' => $matricula
    ]);

    // 10. Consulta o saldo atualizado
    $sqlConsulta = "
        SELECT saldo, atualizado_em
        FROM saldos
        WHERE matricula = :matricula
    ";

    $stmtConsulta = $pdo->prepare($sqlConsulta);

    $stmtConsulta->execute([
        ':matricula' => $matricula
    ]);

    $resultado = $stmtConsulta->fetch(PDO::FETCH_ASSOC);

    // 11. Registra a operação no histórico
    $sqlLog = "
        INSERT INTO logs_alteracoes
            (matricula, operador, valor, saldo_anterior, saldo_posterior)
        VALUES
            (:matricula, :operador, :valor, :saldo_anterior, :saldo_posterior)
    ";

    $stmtLog = $pdo->prepare($sqlLog);

    $stmtLog->execute([
        ':matricula' => $matricula,
        ':operador' => $operador,
        ':valor' => $valor,
        ':saldo_anterior' => $saldoAnterior,
        ':saldo_posterior' => $resultado['saldo']
    ]);

    // 12. Confirma saldo e histórico juntos
    $pdo->commit();

    http_response_code(200);

    echo json_encode([
        'mensagem' => 'Recarga realizada com sucesso',
        'matricula' => $matricula,
        'operador' => $operador,
        'valorAdicionado' => $valor,
        'saldoAnterior' => $saldoAnterior,
        'saldo' => $resultado['saldo'],
        'atualizado_em' => $resultado['atualizado_em']
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);

    echo json_encode([
        'erro' => 'Erro interno ao realizar a recarga'
    ], JSON_UNESCAPED_UNICODE);
}

exit;
