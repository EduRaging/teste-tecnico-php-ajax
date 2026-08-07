<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

function voltarParaLoginComErro(string $mensagem, string $email = ''): void
{
    $_SESSION['erro_login'] = $mensagem;
    $_SESSION['email_login'] = $email;

    header('Location: index.php');
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: index.php');
    exit;
}

$email = trim((string) ($_POST['email'] ?? ''));
$senha = (string) ($_POST['senha'] ?? '');

if ($email === '' || $senha === '') {
    voltarParaLoginComErro('Preencha o e-mail e a senha.', $email);
}

if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    voltarParaLoginComErro('Informe um e-mail válido.', $email);
}

try {
    $conexao = conectarBanco();
    $consulta = $conexao->prepare(
        'SELECT codigo, email, senha, nome, perfil
         FROM usuarios
         WHERE email = :email
         LIMIT 1'
    );
    $consulta->execute(['email' => $email]);
    $usuario = $consulta->fetch();

    if (!$usuario || !password_verify($senha, $usuario['senha'])) {
        voltarParaLoginComErro('E-mail ou senha inválidos.', $email);
    }

    session_regenerate_id(true);

    $_SESSION['usuario'] = [
        'codigo' => (int) $usuario['codigo'],
        'email' => $usuario['email'],
        'nome' => $usuario['nome'],
        'perfil' => $usuario['perfil'],
    ];

    header('Location: dashboard.php');
    exit;
} catch (Throwable $erro) {
    error_log('Falha no login: ' . $erro->getMessage());
    voltarParaLoginComErro('Não foi possível entrar agora. Tente novamente.', $email);
}

