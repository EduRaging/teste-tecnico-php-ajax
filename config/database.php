<?php

declare(strict_types=1);

function obterVariavelAmbiente(string $nome, string $valorPadrao): string
{
    $valor = getenv($nome);

    return $valor === false ? $valorPadrao : $valor;
}

define('DB_HOST', obterVariavelAmbiente('DB_HOST', '127.0.0.1'));
define('DB_PORT', obterVariavelAmbiente('DB_PORT', '3306'));
define('DB_NAME', obterVariavelAmbiente('DB_NAME', 'teste_tecnico'));
define('DB_USER', obterVariavelAmbiente('DB_USER', 'root'));
define('DB_PASS', obterVariavelAmbiente('DB_PASS', ''));

function conectarBanco(): PDO
{
    static $conexao = null;

    if ($conexao instanceof PDO) {
        return $conexao;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_PORT,
        DB_NAME
    );

    $conexao = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $conexao;
}
