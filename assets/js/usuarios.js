(function ($) {
    'use strict';

    $(function () {
        var modalUsuario = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUsuario'));
        var modalExclusao = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalExclusao'));
        var tokenCsrf = $('meta[name="csrf-token"]').attr('content');
        var codigoParaExcluir = null;
        var $formUsuario = $('#formUsuario');
        var $corpoTabela = $('#tabelaUsuarios tbody');

        carregarUsuarios();

        $('#btnNovoUsuario').on('click', function () {
            prepararModalCadastro();
            modalUsuario.show();
        });

        $('#tabelaUsuarios').on('click', '.btn-editar', function () {
            buscarUsuario($(this).data('codigo'));
        });

        $('#tabelaUsuarios').on('click', '.btn-excluir', function () {
            codigoParaExcluir = $(this).data('codigo');
            $('#nomeUsuarioExclusao').text($(this).data('nome'));
            modalExclusao.show();
        });

        $formUsuario.on('submit', function (evento) {
            evento.preventDefault();

            if (!$formUsuario[0].checkValidity()) {
                $formUsuario.addClass('was-validated');
                return;
            }

            salvarUsuario();
        });

        $('#btnConfirmarExclusao').on('click', function () {
            if (codigoParaExcluir !== null) {
                excluirUsuario(codigoParaExcluir);
            }
        });

        function carregarUsuarios() {
            $corpoTabela.html(
                '<tr><td colspan="5" class="text-center py-4">' +
                '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>' +
                'Carregando usuários...</td></tr>'
            );

            $.ajax({
                url: 'api/usuarios/listar.php',
                method: 'GET',
                dataType: 'json'
            }).done(function (resposta) {
                montarTabela(resposta.dados.usuarios);
            }).fail(function (xhr) {
                $corpoTabela.html(
                    '<tr><td colspan="5" class="text-center text-danger py-4">' +
                    'Não foi possível carregar os usuários.</td></tr>'
                );
                tratarErroAjax(xhr, 'Não foi possível carregar os usuários.');
            });
        }

        function montarTabela(usuarios) {
            $corpoTabela.empty();

            if (!usuarios || usuarios.length === 0) {
                $corpoTabela.append(
                    $('<tr>').append(
                        $('<td>', {
                            colspan: 5,
                            class: 'text-center text-body-secondary py-4'
                        }).text('Nenhum usuário cadastrado.')
                    )
                );
                return;
            }

            $.each(usuarios, function (_, usuario) {
                var $acoes = $('<div>', { class: 'btn-group btn-group-sm', role: 'group' });

                $('<button>', {
                    type: 'button',
                    class: 'btn btn-outline-primary btn-editar'
                }).text('Editar').attr('data-codigo', usuario.codigo).appendTo($acoes);

                $('<button>', {
                    type: 'button',
                    class: 'btn btn-outline-danger btn-excluir'
                }).text('Excluir')
                    .attr('data-codigo', usuario.codigo)
                    .attr('data-nome', usuario.nome)
                    .appendTo($acoes);

                $('<tr>')
                    .append($('<td>').text(usuario.codigo))
                    .append($('<td>').text(usuario.nome))
                    .append($('<td>').text(usuario.email))
                    .append($('<td>').append(
                        $('<span>', {
                            class: usuario.perfil === 'ADM'
                                ? 'badge text-bg-primary'
                                : 'badge text-bg-secondary'
                        }).text(usuario.perfil)
                    ))
                    .append($('<td>').append($acoes))
                    .appendTo($corpoTabela);
            });
        }

        function prepararModalCadastro() {
            $formUsuario[0].reset();
            $formUsuario.removeClass('was-validated');
            $('#codigo').val('');
            $('#tituloModalUsuario').text('Novo usuário');
            $('#senha').prop('required', true);
            $('#ajudaSenha').text('Use pelo menos 6 caracteres.');
        }

        function buscarUsuario(codigo) {
            $.ajax({
                url: 'api/usuarios/buscar.php',
                method: 'GET',
                data: { codigo: codigo },
                dataType: 'json'
            }).done(function (resposta) {
                var usuario = resposta.dados.usuario;

                $formUsuario[0].reset();
                $formUsuario.removeClass('was-validated');
                $('#codigo').val(usuario.codigo);
                $('#nome').val(usuario.nome);
                $('#email').val(usuario.email);
                $('#perfil').val(usuario.perfil);
                $('#senha').val('').prop('required', false);
                $('#tituloModalUsuario').text('Editar usuário');
                $('#ajudaSenha').text('Deixe em branco para manter a senha atual.');

                modalUsuario.show();
            }).fail(function (xhr) {
                tratarErroAjax(xhr, 'Não foi possível buscar o usuário.');
            });
        }

        function salvarUsuario() {
            var $botao = $('#btnSalvarUsuario');
            var textoOriginal = $botao.text();

            $botao.prop('disabled', true).text('Salvando...');

            $.ajax({
                url: 'api/usuarios/salvar.php',
                method: 'POST',
                data: $formUsuario.serialize(),
                dataType: 'json'
            }).done(function (resposta) {
                modalUsuario.hide();
                mostrarMensagem('success', resposta.mensagem);
                carregarUsuarios();
            }).fail(function (xhr) {
                tratarErroAjax(xhr, 'Não foi possível salvar o usuário.');
            }).always(function () {
                $botao.prop('disabled', false).text(textoOriginal);
            });
        }

        function excluirUsuario(codigo) {
            var $botao = $('#btnConfirmarExclusao');
            var textoOriginal = $botao.text();

            $botao.prop('disabled', true).text('Excluindo...');

            $.ajax({
                url: 'api/usuarios/excluir.php',
                method: 'POST',
                data: {
                    codigo: codigo,
                    csrf_token: tokenCsrf
                },
                dataType: 'json'
            }).done(function (resposta) {
                modalExclusao.hide();
                codigoParaExcluir = null;
                mostrarMensagem('success', resposta.mensagem);
                carregarUsuarios();
            }).fail(function (xhr) {
                tratarErroAjax(xhr, 'Não foi possível excluir o usuário.');
            }).always(function () {
                $botao.prop('disabled', false).text(textoOriginal);
            });
        }

        function mostrarMensagem(tipo, mensagem) {
            var $alerta = $('<div>', {
                class: 'alert alert-' + tipo + ' alert-dismissible fade show',
                role: 'alert'
            });

            $alerta.append($('<span>').text(mensagem));
            $alerta.append(
                $('<button>', {
                    type: 'button',
                    class: 'btn-close',
                    'data-bs-dismiss': 'alert',
                    'aria-label': 'Fechar'
                })
            );

            $('#areaMensagem').empty().append($alerta);

            window.setTimeout(function () {
                bootstrap.Alert.getOrCreateInstance($alerta[0]).close();
            }, 5000);
        }

        function tratarErroAjax(xhr, mensagemPadrao) {
            if (xhr.status === 401) {
                window.location.href = 'index.php';
                return;
            }

            var mensagem = mensagemPadrao;

            if (xhr.responseJSON && xhr.responseJSON.mensagem) {
                mensagem = xhr.responseJSON.mensagem;
            }

            mostrarMensagem('danger', mensagem);
        }
    });
})(jQuery);
