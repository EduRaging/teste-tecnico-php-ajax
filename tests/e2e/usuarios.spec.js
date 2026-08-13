const { test, expect } = require('@playwright/test');

const ADMIN_EMAIL = 'admin@teste.com';
const ADMIN_SENHA = 'Admin@123';

async function entrarComoAdministrador(page) {
  await page.goto('index.php');
  await page.locator('#email').fill(ADMIN_EMAIL);
  await page.locator('#senha').fill(ADMIN_SENHA);

  const listagem = page.waitForResponse(
    (resposta) => resposta.url().includes('/api/usuarios/listar.php') && resposta.ok()
  );

  await page.getByRole('button', { name: 'Entrar' }).click();
  await listagem;
  await expect(page).toHaveURL(/dashboard\.php$/);
}

test('bloqueia o dashboard para quem não está autenticado', async ({ page }) => {
  await page.goto('dashboard.php');

  await expect(page).toHaveURL(/index\.php$/);
  await expect(page.getByRole('heading', { name: 'Gerenciamento de Usuários' })).toBeVisible();
});

test('mostra uma mensagem amigável quando o login é inválido', async ({ page }) => {
  await page.goto('index.php');
  await page.locator('#email').fill('admin@teste.com');
  await page.locator('#senha').fill('senha-incorreta');
  await page.getByRole('button', { name: 'Entrar' }).click();

  await expect(page).toHaveURL(/index\.php$/);
  await expect(page.getByRole('alert')).toContainText('E-mail ou senha inválidos.');
  await expect(page.locator('#email')).toHaveValue('admin@teste.com');
});

test('faz login, carrega os usuários via Ajax e encerra a sessão', async ({ page }) => {
  await entrarComoAdministrador(page);

  await expect(page.getByRole('heading', { name: 'Usuários' })).toBeVisible();
  await expect(page.locator('#tabelaUsuarios tbody')).toContainText(ADMIN_EMAIL);

  await page.getByRole('button', { name: 'Sair' }).click();
  await expect(page).toHaveURL(/index\.php$/);

  await page.goto('dashboard.php');
  await expect(page).toHaveURL(/index\.php$/);
});

test('cadastra, edita e exclui um usuário via Ajax sem recarregar a página', async ({ page }) => {
  await entrarComoAdministrador(page);

  const identificador = `${Date.now()}-${test.info().workerIndex}`;
  const email = `playwright.${identificador}@teste.com`;
  const nomeInicial = `Usuário Playwright ${identificador}`;
  const nomeEditado = `Usuário Editado ${identificador}`;
  const requisicoesDeDocumento = [];

  page.on('request', (requisicao) => {
    if (requisicao.resourceType() === 'document') {
      requisicoesDeDocumento.push(requisicao.url());
    }
  });

  await page.getByRole('button', { name: 'Novo usuário' }).click();

  const modalUsuario = page.locator('#modalUsuario');
  await expect(modalUsuario).toBeVisible();
  await modalUsuario.locator('#nome').fill(nomeInicial);
  await modalUsuario.locator('#email').fill(email);
  await modalUsuario.locator('#senha').fill('Teste@123');
  await modalUsuario.locator('#perfil').selectOption('USER');

  const cadastro = page.waitForResponse(
    (resposta) => resposta.url().includes('/api/usuarios/salvar.php')
  );
  const listagemAposCadastro = page.waitForResponse(
    (resposta) => resposta.url().includes('/api/usuarios/listar.php') && resposta.ok()
  );

  await modalUsuario.getByRole('button', { name: 'Salvar' }).click();
  expect((await cadastro).status()).toBe(200);
  await listagemAposCadastro;

  await expect(page.getByRole('alert')).toContainText('Usuário cadastrado com sucesso.');
  let linhaUsuario = page.locator('#tabelaUsuarios tbody tr').filter({ hasText: email });
  await expect(linhaUsuario).toContainText(nomeInicial);

  const busca = page.waitForResponse(
    (resposta) => resposta.url().includes('/api/usuarios/buscar.php') && resposta.ok()
  );
  await linhaUsuario.getByRole('button', { name: 'Editar' }).click();
  await busca;

  await expect(modalUsuario).toBeVisible();
  await expect(modalUsuario.locator('#senha')).not.toHaveAttribute('required', '');
  await modalUsuario.locator('#nome').fill(nomeEditado);

  const edicao = page.waitForResponse(
    (resposta) => resposta.url().includes('/api/usuarios/salvar.php')
  );
  const listagemAposEdicao = page.waitForResponse(
    (resposta) => resposta.url().includes('/api/usuarios/listar.php') && resposta.ok()
  );

  await modalUsuario.getByRole('button', { name: 'Salvar' }).click();
  expect((await edicao).status()).toBe(200);
  await listagemAposEdicao;

  linhaUsuario = page.locator('#tabelaUsuarios tbody tr').filter({ hasText: email });
  await expect(linhaUsuario).toContainText(nomeEditado);

  await linhaUsuario.getByRole('button', { name: 'Excluir' }).click();
  const modalExclusao = page.locator('#modalExclusao');
  await expect(modalExclusao).toBeVisible();
  await expect(modalExclusao).toContainText(nomeEditado);

  const exclusao = page.waitForResponse(
    (resposta) => resposta.url().includes('/api/usuarios/excluir.php')
  );
  const listagemAposExclusao = page.waitForResponse(
    (resposta) => resposta.url().includes('/api/usuarios/listar.php') && resposta.ok()
  );

  await modalExclusao.getByRole('button', { name: 'Sim, excluir' }).click();
  expect((await exclusao).status()).toBe(200);
  await listagemAposExclusao;

  await expect(page.locator('#tabelaUsuarios tbody')).not.toContainText(email);
  expect(requisicoesDeDocumento).toEqual([]);
  await expect(page).toHaveURL(/dashboard\.php$/);
});

test('impede o cadastro de um e-mail duplicado', async ({ page }) => {
  await entrarComoAdministrador(page);
  await page.getByRole('button', { name: 'Novo usuário' }).click();

  const modalUsuario = page.locator('#modalUsuario');
  await modalUsuario.locator('#nome').fill('Administrador duplicado');
  await modalUsuario.locator('#email').fill(ADMIN_EMAIL);
  await modalUsuario.locator('#senha').fill('Teste@123');

  const respostaDuplicada = page.waitForResponse(
    (resposta) => resposta.url().includes('/api/usuarios/salvar.php')
  );

  await modalUsuario.getByRole('button', { name: 'Salvar' }).click();

  expect((await respostaDuplicada).status()).toBe(409);
  await expect(page.getByRole('alert')).toContainText('Este e-mail já está cadastrado.');
  await expect(modalUsuario).toBeVisible();
});
