# Sistema de Login e CRUD de Usuários

Teste técnico desenvolvido com PHP, PDO, MySQL/MariaDB, Bootstrap e jQuery/Ajax. O sistema possui autenticação por sessão e um CRUD completo de usuários sem recarregar a página.

## Tecnologias

- PHP 8.0 ou superior;
- MySQL 5.7+/8 ou MariaDB 10+;
- PDO com driver MySQL;
- Bootstrap 5.3.8 via CDN;
- jQuery 3.7.1 via CDN;
- Apache, Nginx ou servidor PHP equivalente.

## Funcionalidades

- Login com e-mail e senha;
- Senhas protegidas por `password_hash()` e `password_verify()`;
- Dashboard protegido por sessão;
- Listagem de usuários via Ajax;
- Cadastro e edição no mesmo modal Bootstrap;
- Exclusão com modal de confirmação;
- Mensagens amigáveis para validação e e-mail duplicado;
- Atualização da tabela sem reload;
- Proteção contra SQL Injection e CSRF.

## Instalação rápida com XAMPP no Windows

1. Baixe e instale o XAMPP 8.2.12 de 64 bits pelo site oficial:
   <https://www.apachefriends.org/pt_br/download.html>
2. Abra o **XAMPP Control Panel**.
3. Inicie os serviços **Apache** e **MySQL**.
4. Copie a pasta `teste-tecnico` para:

   ```text
   C:\xampp\htdocs\teste-tecnico
   ```

5. Abra o phpMyAdmin no navegador:

   ```text
   http://localhost/phpmyadmin/
   ```

6. Clique em **Importar**, selecione o arquivo `banco.sql` e confirme a importação.
7. Acesse o sistema:

   ```text
   http://localhost/teste-tecnico/
   ```

O XAMPP utiliza MariaDB, que é compatível com o SQL e com o driver PDO MySQL usados neste projeto. A aplicação também pode ser executada em um servidor MySQL.

## Credenciais de teste

```text
E-mail: admin@teste.com
Senha: Admin@123
```

A senha acima aparece somente como instrução de teste. No banco ela é armazenada como hash bcrypt.

## Configuração do banco

Os dados de conexão ficam em `config/database.php`:

```php
const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'teste_tecnico';
const DB_USER = 'root';
const DB_PASS = '';
```

Esses são os valores padrão de uma instalação local do XAMPP. Caso sua instalação tenha uma senha para o usuário `root` ou use outra porta, altere essas constantes.

## Estrutura do projeto

```text
teste-tecnico/
├── api/usuarios/
│   ├── buscar.php
│   ├── excluir.php
│   ├── listar.php
│   └── salvar.php
├── assets/
│   ├── css/app.css
│   └── js/usuarios.js
├── config/database.php
├── includes/auth.php
├── banco.sql
├── dashboard.php
├── index.php
├── login.php
├── logout.php
└── README.md
```


## Como o sistema funciona

### Login

`index.php` exibe o formulário. `login.php` procura o usuário pelo e-mail usando uma consulta preparada e compara a senha com `password_verify()`. Se as credenciais forem válidas, o PHP regenera o ID da sessão e redireciona para `dashboard.php`.

### Controle de sessão

`includes/auth.php` inicia a sessão e fornece funções compartilhadas para proteger páginas e endpoints. Sem sessão válida, a página redireciona para o login e os endpoints devolvem JSON com status `401`.

### CRUD via Ajax

`assets/js/usuarios.js` utiliza jQuery para chamar os endpoints da pasta `api/usuarios`. O PHP responde em JSON, e o JavaScript atualiza a tabela e os modais sem recarregar a página.

### Senhas

No cadastro, a senha é transformada em hash com `password_hash()`. Na edição, deixar a senha vazia mantém o hash atual; informar uma nova senha gera um novo hash.

### SQL Injection

Os valores recebidos do usuário são enviados ao banco por parâmetros em consultas preparadas do PDO. Eles não são concatenados diretamente no SQL.

### CSRF

O dashboard cria um token aleatório ligado à sessão. Cadastro, edição, exclusão e logout enviam esse token, que é comparado no servidor com `hash_equals()`.

## Teste manual sugerido

1. Faça login com o administrador inicial.
2. Cadastre um usuário `USER`.
3. Confirme que a tabela foi atualizada sem reload.
4. Tente cadastrar novamente o mesmo e-mail e confira a mensagem.
5. Edite o nome sem informar uma nova senha.
6. Saia e confirme que a senha anterior ainda funciona.
7. Edite o usuário novamente e informe uma nova senha.
8. Exclua o usuário e confira o modal de confirmação.
9. Tente abrir `dashboard.php` depois de sair.

## Verificação de sintaxe

No Prompt de Comando do Windows, execute dentro da pasta do projeto:

```bat
C:\xampp\php\php.exe -l index.php
C:\xampp\php\php.exe -l login.php
C:\xampp\php\php.exe -l dashboard.php
C:\xampp\php\php.exe -l logout.php
C:\xampp\php\php.exe -l config\database.php
C:\xampp\php\php.exe -l includes\auth.php
C:\xampp\php\php.exe -l api\usuarios\listar.php
C:\xampp\php\php.exe -l api\usuarios\buscar.php
C:\xampp\php\php.exe -l api\usuarios\salvar.php
C:\xampp\php\php.exe -l api\usuarios\excluir.php
```

Cada comando deve exibir `No syntax errors detected`.

## Publicação no GitHub

Depois de criar um repositório público vazio no GitHub, execute na pasta do projeto:

```bash
git init
git add .
git commit -m "Implementa login e CRUD de usuários via Ajax"
git branch -M main
git remote add origin URL_DO_REPOSITORIO
git push -u origin main
```

Antes do envio, confirme que nenhuma senha real foi adicionada ao código. As credenciais deste README são exclusivamente para o ambiente de demonstração.
