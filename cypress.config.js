import { defineConfig } from 'cypress'

export default defineConfig({
  e2e: {
    baseUrl: 'http://localhost:8004',
    setupNodeEvents(on, config) {
      // implement node event listeners here
      return config
    },
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'cypress/support/e2e.js',
    viewportWidth: 1280,
    viewportHeight: 720,
    defaultCommandTimeout: 10000,
    pageLoadTimeout: 30000,
    waitForAnimations: true,
    animationDistanceThreshold: 50,
    watchForFileChanges: false,
    chromeWebSecurity: false,
  },
})
