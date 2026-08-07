<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

exigirAutenticacaoApi();
exigirMetodoApi('GET');

$codigo = filter_var(
    $_GET['codigo'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($codigo === false || $codigo === null) {
    responderJson(false, 'Código de usuário inválido.', null, 400);
}

try {
    $conexao = conectarBanco();
    $consulta = $conexao->prepare(
        'SELECT codigo, nome, email, perfil
         FROM usuarios
         WHERE codigo = :codigo
         LIMIT 1'
    );
    $consulta->execute(['codigo' => $codigo]);
    $usuario = $consulta->fetch();

    if (!$usuario) {
        responderJson(false, 'Usuário não encontrado.', null, 404);
    }

    responderJson(true, 'Usuário encontrado.', ['usuario' => $usuario]);
} catch (Throwable $erro) {
    error_log('Falha ao buscar usuário: ' . $erro->getMessage());
    responderJson(false, 'Não foi possível buscar o usuário.', null, 500);
}

