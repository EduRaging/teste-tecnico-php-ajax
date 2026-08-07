# Guia Rápido para Entender e Apresentar o Projeto

Este guia explica somente o necessário para demonstrar o teste e responder às perguntas mais prováveis da entrevista.

## 1. Resumo do projeto em 30 segundos

Uma boa explicação inicial seria:

> O sistema foi desenvolvido em PHP com PDO e MySQL. O login valida a senha armazenada como hash e cria uma sessão. Depois do login, o dashboard protegido utiliza jQuery e Ajax para listar, cadastrar, editar e excluir usuários sem recarregar a página. O Bootstrap foi usado na interface e nos modais. As consultas com dados externos são preparadas para evitar SQL Injection.

## 2. Fluxo do login

1. O navegador abre `index.php`.
2. O usuário informa e-mail e senha.
3. O formulário envia um `POST` para `login.php`.
4. `login.php` busca o usuário pelo e-mail.
5. `password_verify()` compara a senha digitada com o hash do banco.
6. Se estiver correta, o PHP executa `session_regenerate_id(true)`.
7. Os dados básicos do usuário são gravados em `$_SESSION`.
8. O navegador é redirecionado para `dashboard.php`.

O login usa um envio tradicional porque o redirecionamento faz parte do fluxo. As operações do CRUD são as que obrigatoriamente usam Ajax e não recarregam a página.

## 3. Fluxo de uma operação Ajax

O cadastro funciona assim:

1. O usuário abre o modal **Novo usuário**.
2. O JavaScript impede o envio tradicional do formulário com `preventDefault()`.
3. `$.ajax()` envia os campos para `api/usuarios/salvar.php`.
4. O PHP valida os dados.
5. O PDO executa o `INSERT`.
6. O endpoint devolve uma resposta JSON.
7. O JavaScript fecha o modal.
8. A lista é solicitada novamente por Ajax.
9. Somente a tabela é reconstruída; a página não é recarregada.

Edição e exclusão seguem a mesma ideia.

## 4. O que é Ajax neste projeto

Ajax não é uma linguagem. É a realização de uma requisição HTTP pelo JavaScript sem navegar para outra página.

Exemplo simplificado do projeto:

```javascript
$.ajax({
    url: 'api/usuarios/listar.php',
    method: 'GET',
    dataType: 'json'
}).done(function (resposta) {
    montarTabela(resposta.dados.usuarios);
}).fail(function () {
    mostrarMensagem('danger', 'Não foi possível carregar os usuários.');
});
```

Significado de cada parte:

- `url`: arquivo PHP que receberá a requisição;
- `method`: método HTTP utilizado;
- `dataType: 'json'`: formato esperado na resposta;
- `.done()`: executado quando a requisição funciona;
- `.fail()`: executado quando ocorre um erro.

## 5. O que é jQuery neste projeto

jQuery simplifica operações comuns do JavaScript.

Exemplos usados:

```javascript
$('#nome').val(usuario.nome);
```

Procura o elemento com `id="nome"` e altera o valor do campo.

```javascript
$('#btnNovoUsuario').on('click', function () {
    // Ação executada no clique.
});
```

Registra o comportamento de um botão.

```javascript
$('<td>').text(usuario.nome);
```

Cria uma célula e insere o nome como texto. Usar `.text()` evita que um nome contendo HTML seja interpretado pelo navegador.

O símbolo `$` representa a função principal do jQuery.

## 6. O que é Bootstrap neste projeto

Bootstrap fornece classes de CSS e componentes JavaScript prontos.

Exemplos:

- `btn btn-primary`: botão azul;
- `form-control`: aparência dos campos;
- `table-responsive`: tabela adaptável a telas menores;
- `alert alert-danger`: mensagem de erro;
- `modal`: janela de cadastro, edição ou confirmação.

O JavaScript abre um modal desta forma:

```javascript
var modalUsuario = bootstrap.Modal.getOrCreateInstance(
    document.getElementById('modalUsuario')
);

modalUsuario.show();
```

O mesmo modal de usuário atende cadastro e edição. O JavaScript apenas muda o título, o código e os valores dos campos.

## 7. Como o PHP responde ao JavaScript

Os endpoints chamam `responderJson()`, definida em `includes/auth.php`.

Resposta de sucesso:

```json
{
  "sucesso": true,
  "mensagem": "Usuário cadastrado com sucesso.",
  "dados": {
    "codigo": 2
  }
}
```

Resposta de erro:

```json
{
  "sucesso": false,
  "mensagem": "Este e-mail já está cadastrado."
}
```

JSON é um formato de texto organizado em chaves e valores. Ele facilita a comunicação entre PHP e JavaScript.

## 8. PDO e consultas preparadas

PDO é a interface utilizada pelo PHP para acessar o banco.

Exemplo:

```php
$consulta = $conexao->prepare(
    'SELECT codigo, nome FROM usuarios WHERE email = :email'
);

$consulta->execute(['email' => $email]);
```

`:email` é um parâmetro. O valor recebido não é colado diretamente no SQL. O PDO envia a instrução e o valor de forma separada, reduzindo o risco de SQL Injection.

Resposta curta para entrevista:

> Usei consultas preparadas porque dados recebidos do usuário não devem ser concatenados no SQL.

## 9. Proteção das senhas

No cadastro:

```php
$hashSenha = password_hash($senha, PASSWORD_DEFAULT);
```

No login:

```php
password_verify($senha, $usuario['senha']);
```

O banco não guarda a senha original. Ele guarda um hash apropriado para senhas.

Não se compara a senha diretamente no SQL porque o hash pode usar um salt diferente a cada geração. O PHP localiza o usuário pelo e-mail e depois utiliza `password_verify()`.

## 10. Sessão

Depois do login, o sistema grava:

```php
$_SESSION['usuario'] = [
    'codigo' => 1,
    'email' => 'admin@teste.com',
    'nome' => 'Administrador',
    'perfil' => 'ADM',
];
```

O navegador recebe apenas o identificador da sessão. Os dados ficam no servidor.

`dashboard.php` chama `exigirAutenticacaoPagina()`. Os endpoints chamam `exigirAutenticacaoApi()`. Dessa forma, esconder apenas o menu não seria suficiente: o backend também bloqueia acessos sem sessão.

## 11. Token CSRF

O sistema cria um valor aleatório e guarda na sessão. Esse valor acompanha cadastro, edição, exclusão e logout.

No backend, ele é comparado com:

```php
hash_equals($tokenDaSessao, $tokenRecebido);
```

Isso dificulta que outro site faça uma alteração usando a sessão aberta do usuário.

## 12. Por que a senha fica vazia na edição

O hash nunca deve ser enviado ao navegador. Por isso, o endpoint de busca retorna somente código, nome, e-mail e perfil.

Na edição:

- senha vazia: o `UPDATE` não altera a coluna `senha`;
- nova senha informada: o PHP gera um novo hash e atualiza a coluna.

## 13. Como o e-mail duplicado é tratado

Antes do `INSERT` ou `UPDATE`, o sistema consulta se o e-mail já pertence a outro usuário. O banco também possui uma restrição `UNIQUE`.

Existem duas proteções porque a validação melhora a mensagem para o usuário, enquanto a restrição do banco garante a integridade dos dados.

## 14. Responsabilidade dos arquivos

| Arquivo | Responsabilidade |
| --- | --- |
| `index.php` | formulário de login |
| `login.php` | validação das credenciais |
| `dashboard.php` | HTML da área autenticada e dos modais |
| `logout.php` | encerramento da sessão |
| `config/database.php` | conexão PDO |
| `includes/auth.php` | sessão, autenticação, JSON e CSRF |
| `api/usuarios/listar.php` | consulta da lista |
| `api/usuarios/buscar.php` | consulta de um usuário |
| `api/usuarios/salvar.php` | cadastro e edição |
| `api/usuarios/excluir.php` | exclusão |
| `assets/js/usuarios.js` | Ajax, tabela, mensagens e modais |
| `banco.sql` | criação do banco e administrador inicial |

## 15. Demonstração recomendada

Durante a apresentação:

1. Mostre o banco e destaque que a senha é um hash.
2. Faça login.
3. Cadastre um usuário.
4. Mostre que a página não recarregou.
5. Tente repetir o e-mail para demonstrar o tratamento de erro.
6. Edite o usuário sem trocar a senha.
7. Abra a confirmação de exclusão e primeiro selecione **Não**.
8. Abra novamente e confirme a exclusão.
9. Faça logout.
10. Tente acessar diretamente `dashboard.php`.

Se pedirem evidência do Ajax, abra as Ferramentas do Desenvolvedor do navegador, acesse a aba **Network** e repita uma operação. A chamada ao endpoint aparecerá sem navegação para outra página.

## 16. O que não afirmar

- Não diga que Bootstrap é uma linguagem; ele é um toolkit de interface.
- Não diga que Ajax é um banco ou uma biblioteca; é uma técnica de requisições assíncronas.
- Não diga que o hash pode ser descriptografado; a validação é feita por comparação segura.
- Não diga que a validação do navegador garante segurança; a validação definitiva está no PHP.
- Não diga que o perfil `ADM` possui permissões diferentes. O campo existe, mas o enunciado não pediu controle de autorização por perfil.

