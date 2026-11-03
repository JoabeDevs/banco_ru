
<?php

$envPath = __DIR__ . '/.env';

if (!file_exists($envPath)) {
    die("Erro: arquivo .env não encontrado.");
}

$env = parse_ini_file($envPath);

if ($env === false) {
    die("Erro ao ler o arquivo .env.");
}

$required = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME'];

foreach ($required as $key) {
    if (!array_key_exists($key, $env)) {
        die("Erro: configuração ausente no arquivo .env.");
    }
}

$dbHost = $env['DB_HOST'];
$dbUser = $env['DB_USER'];
$dbPass = $env['DB_PASS'];
$dbName = $env['DB_NAME'];

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    error_log("Erro de conexão com o banco: " . $e->getMessage());
    die("Erro ao conectar ao banco. Verifique o log do PHP.");
}
    