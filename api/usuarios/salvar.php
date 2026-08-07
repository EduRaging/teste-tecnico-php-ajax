<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

exigirAutenticacaoApi();
exigirMetodoApi('POST');

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    responderJson(false, 'Token de segurança inválido. Atualize a página.', null, 403);
}

$codigoInformado = trim((string) ($_POST['codigo'] ?? ''));
$nome = trim((string) ($_POST['nome'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$senha = (string) ($_POST['senha'] ?? '');
$perfil = strtoupper(trim((string) ($_POST['perfil'] ?? '')));
$codigo = null;
$errosValidacao = [];

if ($codigoInformado !== '') {
    $codigoValidado = filter_var(
        $codigoInformado,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($codigoValidado === false) {
        $errosValidacao[] = 'O código informado é inválido.';
    } else {
        $codigo = (int) $codigoValidado;
    }
}

if ($nome === '') {
    $errosValidacao[] = 'O nome é obrigatório.';
} elseif (mb_strlen($nome) > 150) {
    $errosValidacao[] = 'O nome deve possuir no máximo 150 caracteres.';
}

if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    $errosValidacao[] = 'Informe um e-mail válido.';
} elseif (mb_strlen($email) > 255) {
    $errosValidacao[] = 'O e-mail deve possuir no máximo 255 caracteres.';
}

if (!in_array($perfil, ['ADM', 'USER'], true)) {
    $errosValidacao[] = 'O perfil deve ser ADM ou USER.';
}

if ($codigo === null && strlen($senha) < 6) {
    $errosValidacao[] = 'A senha deve possuir pelo menos 6 caracteres.';
}

if ($codigo !== null && $senha !== '' && strlen($senha) < 6) {
    $errosValidacao[] = 'A nova senha deve possuir pelo menos 6 caracteres.';
}

if ($errosValidacao !== []) {
    responderJson(
        false,
        $errosValidacao[0],
        ['erros' => $errosValidacao],
        422
    );
}

try {
    $conexao = conectarBanco();

    $sqlEmail = 'SELECT codigo FROM usuarios WHERE email = :email';
    $parametrosEmail = ['email' => $email];

    if ($codigo !== null) {
        $sqlEmail .= ' AND codigo <> :codigo';
        $parametrosEmail['codigo'] = $codigo;
    }

    $consultaEmail = $conexao->prepare($sqlEmail . ' LIMIT 1');
    $consultaEmail->execute($parametrosEmail);

    if ($consultaEmail->fetch()) {
        responderJson(false, 'Este e-mail já está cadastrado.', null, 409);
    }

    if ($codigo === null) {
        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);

        if ($hashSenha === false) {
            throw new RuntimeException('Não foi possível gerar o hash da senha.');
        }

        $comando = $conexao->prepare(
            'INSERT INTO usuarios (nome, email, senha, perfil)
             VALUES (:nome, :email, :senha, :perfil)'
        );
        $comando->execute([
            'nome' => $nome,
            'email' => $email,
            'senha' => $hashSenha,
            'perfil' => $perfil,
        ]);

        responderJson(
            true,
            'Usuário cadastrado com sucesso.',
            ['codigo' => (int) $conexao->lastInsertId()]
        );
    }

    $consultaUsuario = $conexao->prepare(
        'SELECT codigo FROM usuarios WHERE codigo = :codigo LIMIT 1'
    );
    $consultaUsuario->execute(['codigo' => $codigo]);

    if (!$consultaUsuario->fetch()) {
        responderJson(false, 'Usuário não encontrado.', null, 404);
    }

    if ($senha === '') {
        $comando = $conexao->prepare(
            'UPDATE usuarios
             SET nome = :nome, email = :email, perfil = :perfil
             WHERE codigo = :codigo'
        );
        $parametros = [
            'nome' => $nome,
            'email' => $email,
            'perfil' => $perfil,
            'codigo' => $codigo,
        ];
    } else {
        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);

        if ($hashSenha === false) {
            throw new RuntimeException('Não foi possível gerar o hash da senha.');
        }

        $comando = $conexao->prepare(
            'UPDATE usuarios
             SET nome = :nome, email = :email, senha = :senha, perfil = :perfil
             WHERE codigo = :codigo'
        );
        $parametros = [
            'nome' => $nome,
            'email' => $email,
            'senha' => $hashSenha,
            'perfil' => $perfil,
            'codigo' => $codigo,
        ];
    }

    $comando->execute($parametros);

    responderJson(true, 'Usuário atualizado com sucesso.', ['codigo' => $codigo]);
} catch (PDOException $erro) {
    error_log('Falha de banco ao salvar usuário: ' . $erro->getMessage());

    if ($erro->getCode() === '23000') {
        responderJson(false, 'Este e-mail já está cadastrado.', null, 409);
    }

    responderJson(false, 'Não foi possível salvar o usuário.', null, 500);
} catch (Throwable $erro) {
    error_log('Falha ao salvar usuário: ' . $erro->getMessage());
    responderJson(false, 'Não foi possível salvar o usuário.', null, 500);
}

