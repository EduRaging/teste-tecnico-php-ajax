<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

exigirAutenticacaoPagina();

$tokenCsrf = obterTokenCsrf();
$nomeUsuarioLogado = (string) ($_SESSION['usuario']['nome'] ?? 'Usuário');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8') ?>">
    <title>Usuários | Dashboard</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
        crossorigin="anonymous"
    >
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-dark navbar-dark shadow-sm">
    <div class="container">
        <span class="navbar-brand">Sistema de Usuários</span>
        <div class="d-flex align-items-center gap-3">
            <span class="text-white d-none d-sm-inline">
                Olá, <?= htmlspecialchars($nomeUsuarioLogado, ENT_QUOTES, 'UTF-8') ?>
            </span>
            <form action="logout.php" method="post" class="m-0">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-outline-light btn-sm">Sair</button>
            </form>
        </div>
    </div>
</nav>

<main class="container py-4 py-md-5">
    <div id="areaMensagem" aria-live="polite"></div>

    <div class="card dashboard-card shadow-sm">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
                <div>
                    <h1 class="h3 mb-1">Usuários</h1>
                    <p class="text-body-secondary mb-0">Cadastre e gerencie os usuários do sistema.</p>
                </div>
                <button type="button" class="btn btn-primary" id="btnNovoUsuario">
                    Novo usuário
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tabelaUsuarios">
                    <thead class="table-light">
                    <tr>
                        <th scope="col">Código</th>
                        <th scope="col">Nome</th>
                        <th scope="col">E-mail</th>
                        <th scope="col">Perfil</th>
                        <th scope="col" class="acoes-coluna">Ações</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="5" class="text-center py-4">Carregando...</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="tituloModalUsuario" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formUsuario" novalidate>
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="tituloModalUsuario">Novo usuário</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="codigo" name="codigo">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" maxlength="150" required>
                        <div class="invalid-feedback">Informe o nome.</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" maxlength="255" required>
                        <div class="invalid-feedback">Informe um e-mail válido.</div>
                    </div>

                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" minlength="6" autocomplete="new-password">
                        <div class="form-text" id="ajudaSenha">Use pelo menos 6 caracteres.</div>
                        <div class="invalid-feedback">Informe uma senha com pelo menos 6 caracteres.</div>
                    </div>

                    <div>
                        <label for="perfil" class="form-label">Perfil</label>
                        <select class="form-select" id="perfil" name="perfil" required>
                            <option value="USER">USER</option>
                            <option value="ADM">ADM</option>
                        </select>
                        <div class="invalid-feedback">Selecione um perfil.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnSalvarUsuario">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalExclusao" tabindex="-1" aria-labelledby="tituloModalExclusao" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="tituloModalExclusao">Confirmar exclusão</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                Deseja realmente excluir <strong id="nomeUsuarioExclusao"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Não</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">Sim, excluir</button>
            </div>
        </div>
    </div>
</div>

<script
    src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
    crossorigin="anonymous"
></script>
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"
></script>
<script src="assets/js/usuarios.js"></script>
</body>
</html>

