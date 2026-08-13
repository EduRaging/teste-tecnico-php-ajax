const { defineConfig, devices } = require('@playwright/test');

const urlInformada = process.env.BASE_URL;
const baseURL = (urlInformada || 'http://127.0.0.1:8000').replace(/\/?$/, '/');

module.exports = defineConfig({
  testDir: './tests/e2e',
  fullyParallel: false,
  forbidOnly: Boolean(process.env.CI),
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: process.env.CI
    ? [['line'], ['html', { open: 'never' }]]
    : [['list'], ['html', { open: 'never' }]],

  use: {
    baseURL,
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure'
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] }
    }
  ],

  webServer: urlInformada
    ? undefined
    : {
        command: `${process.env.PHP_BINARY || 'php'} -S 127.0.0.1:8000`,
        url: baseURL,
        reuseExistingServer: !process.env.CI,
        timeout: 120000
      }
});
