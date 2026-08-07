<?php

declare(strict_types=1);

function iniciarSessaoSegura(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $usaHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $usaHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

iniciarSessaoSegura();

function usuarioEstaAutenticado(): bool
{
    return isset($_SESSION['usuario']['codigo']);
}

function exigirAutenticacaoPagina(): void
{
    if (!usuarioEstaAutenticado()) {
        header('Location: index.php');
        exit;
    }
}

function responderJson(
    bool $sucesso,
    string $mensagem,
    ?array $dados = null,
    int $statusHttp = 200
): void {
    http_response_code($statusHttp);
    header('Content-Type: application/json; charset=utf-8');

    $resposta = [
        'sucesso' => $sucesso,
        'mensagem' => $mensagem,
    ];

    if ($dados !== null) {
        $resposta['dados'] = $dados;
    }

    echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function exigirAutenticacaoApi(): void
{
    if (!usuarioEstaAutenticado()) {
        responderJson(false, 'Sua sessão expirou. Entre novamente.', null, 401);
    }
}

function exigirMetodoApi(string $metodoEsperado): void
{
    $metodoRecebido = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if ($metodoRecebido !== strtoupper($metodoEsperado)) {
        responderJson(false, 'Método de requisição não permitido.', null, 405);
    }
}

function obterTokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validarTokenCsrf(?string $tokenRecebido): bool
{
    $tokenDaSessao = $_SESSION['csrf_token'] ?? '';

    return $tokenDaSessao !== ''
        && $tokenRecebido !== null
        && hash_equals($tokenDaSessao, $tokenRecebido);
}

