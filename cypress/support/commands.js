// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************

// Custom command for login
Cypress.Commands.add('login', (email, password, remember = false) => {
  cy.visit('/login')
  cy.get('#email').type(email)
  cy.get('#password').type(password)
  if (remember) {
    cy.get('#remember_me').check()
  }
  cy.get('form').submit()
})

// Custom command for registration
Cypress.Commands.add('register', ({ name, email, location, password }) => {
  cy.visit('/register')
  cy.get('#name').type(name)
  cy.get('#email').type(email)
  cy.get('#location').select(location)
  cy.get('#password').type(password)
  cy.get('#password_confirmation').type(password)
  cy.get('form').submit()
})
