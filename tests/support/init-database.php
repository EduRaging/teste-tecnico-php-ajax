<?php

declare(strict_types=1);

function obterConfiguracao(string $nome, string $padrao): string
{
    $valor = getenv($nome);

    return $valor === false ? $padrao : $valor;
}

$host = obterConfiguracao('DB_HOST', '127.0.0.1');
$porta = obterConfiguracao('DB_PORT', '3306');
$usuario = obterConfiguracao('DB_USER', 'root');
$senha = obterConfiguracao('DB_PASS', '');
$arquivoSql = dirname(__DIR__, 2) . '/banco.sql';
$sql = file_get_contents($arquivoSql);

if ($sql === false) {
    throw new RuntimeException('Não foi possível ler o arquivo banco.sql.');
}

$conexao = new PDO(
    "mysql:host={$host};port={$porta};charset=utf8mb4",
    $usuario,
    $senha,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]
);

$conexao->exec($sql);

echo "Banco de testes preparado com sucesso.\n";
