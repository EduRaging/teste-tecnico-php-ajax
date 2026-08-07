<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

exigirAutenticacaoApi();
exigirMetodoApi('GET');

try {
    $conexao = conectarBanco();
    $consulta = $conexao->query(
        'SELECT codigo, nome, email, perfil
         FROM usuarios
         ORDER BY nome ASC, codigo ASC'
    );

    $usuarios = $consulta->fetchAll();

    responderJson(
        true,
        'Usuários carregados com sucesso.',
        ['usuarios' => $usuarios]
    );
} catch (Throwable $erro) {
    error_log('Falha ao listar usuários: ' . $erro->getMessage());
    responderJson(false, 'Não foi possível carregar os usuários.', null, 500);
}

