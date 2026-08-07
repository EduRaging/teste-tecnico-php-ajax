<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (usuarioEstaAutenticado()) {
    header('Location: dashboard.php');
    exit;
}

$erroLogin = $_SESSION['erro_login'] ?? null;
$emailAnterior = $_SESSION['email_login'] ?? '';

unset($_SESSION['erro_login'], $_SESSION['email_login']);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Gerenciamento de Usuários</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
    >
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<main class="login-page py-5">
    <div class="container">
        <div class="card login-card shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h1 class="h3 mb-2">Gerenciamento de Usuários</h1>
                    <p class="text-body-secondary mb-0">Entre para acessar o sistema</p>
                </div>

                <?php if ($erroLogin !== null): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars((string) $erroLogin, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars((string) $emailAnterior, ENT_QUOTES, 'UTF-8') ?>"
                            maxlength="255"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </div>

                    <div class="mb-4">
                        <label for="senha" class="form-label">Senha</label>
                        <input
                            type="password"
                            class="form-control"
                            id="senha"
                            name="senha"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</main>
</body>
</html>

