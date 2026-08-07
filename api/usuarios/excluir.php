<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

exigirAutenticacaoApi();
exigirMetodoApi('POST');

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    responderJson(false, 'Token de segurança inválido. Atualize a página.', null, 403);
}

$codigo = filter_var(
    $_POST['codigo'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($codigo === false || $codigo === null) {
    responderJson(false, 'Código de usuário inválido.', null, 400);
}

try {
    $conexao = conectarBanco();
    $comando = $conexao->prepare('DELETE FROM usuarios WHERE codigo = :codigo');
    $comando->execute(['codigo' => $codigo]);

    if ($comando->rowCount() === 0) {
        responderJson(false, 'Usuário não encontrado.', null, 404);
    }

    responderJson(true, 'Usuário excluído com sucesso.');
} catch (Throwable $erro) {
    error_log('Falha ao excluir usuário: ' . $erro->getMessage());
    responderJson(false, 'Não foi possível excluir o usuário.', null, 500);
}

