<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

if (!validarTokenCsrf($_POST['csrf_token'] ?? null)) {
    header('Location: dashboard.php');
    exit;
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $parametrosCookie = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parametrosCookie['path'],
        $parametrosCookie['domain'],
        (bool) $parametrosCookie['secure'],
        (bool) $parametrosCookie['httponly']
    );
}

session_destroy();

header('Location: index.php');
exit;

