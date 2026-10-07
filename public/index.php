<?php

echo $_SERVER['REQUEST_METHOD'];
exit;

header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'erro' => 'Método não permitido'
    ]);

    exit;
}

header('Content-Type: application/json; charset=utf-8');

// 1. Verifica a chave de API
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

if ($apiKey !== $env['API_KEY']) {
    http_response_code(401);

    echo json_encode([
        'erro' => 'Chave de API inválida'
    ]);

    exit;
}

// 2. Lê o JSON enviado pelo Postman
$body = file_get_contents('php://input');
$dados = json_decode($body, true);

// Verifica se o JSON é válido
if (!is_array($dados)) {
    http_response_code(400);

    echo json_encode([
        'erro' => 'Body inválido'
    ]);

    exit;
}

// Verifica se matrícula foi enviada
if (!isset($dados['matricula'])) {
    http_response_code(400);

    echo json_encode([
        'erro' => "Campo 'matricula' é obrigatório"
    ]);

    exit;
}

// Verifica se data foi enviada
if (!isset($dados['data'])) {
    http_response_code(400);

    echo json_encode([
        'erro' => "Campo 'data' é obrigatório"
    ]);

    exit;
}

// Verifica se matrícula possui exatamente 11 dígitos
$matricula = (string) $dados['matricula'];

if (!preg_match('/^\d{11}$/', $matricula)) {
    http_response_code(400);

    echo json_encode([
        'erro' => "Campo 'matricula' deve possuir 11 dígitos"
    ]);

    exit;
}

// Verifica se a data é válida
$data = $dados['data'];

$dataFormatada = DateTime::createFromFormat('Y-m-d\TH:i:s', $data);
$errosData = DateTime::getLastErrors();

if (
    !$dataFormatada ||
    ($errosData !== false &&
    ($errosData['warning_count'] > 0 || $errosData['error_count'] > 0))
) {
    http_response_code(400);

    echo json_encode([
        'erro' => "Campo 'data' possui formato inválido"
    ]);

    exit;
}

// Se chegou aqui, a validação passou
// Busca os dados do aluno no banco
$sql = "
    SELECT 
        alunos.matricula,
        alunos.nome,
        alunos.foto,
        alunos.foto_atualizada_em,
        saldos.saldo,
        saldos.atualizado_em,
        qrcodes.codigo,
        qrcodes.gerado_em
    FROM alunos
    INNER JOIN saldos
        ON alunos.matricula = saldos.matricula
    INNER JOIN qrcodes
        ON alunos.matricula = qrcodes.matricula
    WHERE alunos.matricula = :matricula
";

try {
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':matricula' => $matricula
    ]);

    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'erro' => 'Erro interno'
    ]);

    exit;
}

if (!$aluno) {
    http_response_code(404);

    echo json_encode([
        'erro' => 'Matrícula não encontrada'
    ]);

    exit;
}

// Verifica se a foto foi atualizada depois da data informada
$foto = null;

if ($aluno['foto_atualizada_em'] > str_replace('T', ' ', $data)) {

    if (empty($aluno['foto'])) {
        http_response_code(400);

        echo json_encode([
            'erro' => "Campo 'foto' é obrigatório"
        ]);

        exit;
    }

    $foto = $aluno['foto'];
}

// Verifica se o QR Code foi gerado depois da data informada
$qrCode = null;

if ($aluno['gerado_em'] > str_replace('T', ' ', $data)) {

    if (empty($aluno['codigo'])) {
        http_response_code(400);

        echo json_encode([
            'erro' => "Campo 'strQrCode' é obrigatório"
        ]);

        exit;
    }

    $qrCode = $aluno['codigo'];
}

echo json_encode([
    'matricula' => $aluno['matricula'],
    'nome' => $aluno['nome'],
    'saldo' => $aluno['saldo'],
    'DHAtualizacaoSaldo' => $aluno['atualizado_em'],
    'strQrCode' => $qrCode,
    'foto' => $foto
]);

exit;