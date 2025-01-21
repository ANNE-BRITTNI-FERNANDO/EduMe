describe('User Role Management', () => {
  beforeEach(() => {
    // Clear any existing session by making a POST request to logout
    cy.request({
      method: 'POST',
      url: '/logout',
      failOnStatusCode: false
    })
    
    // Register a new user
    const timestamp = new Date().getTime()
    const email = `test${timestamp}@example.com`
    const password = 'Password123!'

    cy.visit('/register')
    cy.get('form').should('be.visible')
    cy.get('#name').type('Test User')
    cy.get('#email').type(email)
    cy.get('#location').select('Colombo')
    cy.get('#password').type(password)
    cy.get('#password_confirmation').type(password)
    cy.get('form').submit()

    // After registration, we should be on dashboard1
    cy.url().should('include', '/dashboard1')
    
    // Wait for page to be fully loaded
    cy.get('div.bg-white').should('be.visible')
  })

  describe('Role Switching', () => {
    describe('Initial Role Selection', () => {
      it('should allow selecting buyer role', () => {
        // Find the buyer button by its text and click it
        cy.contains('button', "I'm a Buyer").should('be.visible').click()

        // Should redirect to productlisting page
        cy.url().should('include', '/productlisting')
        // Verify buyer navigation items
        cy.get('nav').should('contain', 'Product')
        cy.get('nav').should('contain', 'Cart')
      })

      it('should allow selecting seller role', () => {
        // Find the seller button by its text and click it
        cy.contains('button', "I'm a Seller").should('be.visible').click()

        // Should redirect to seller dashboard
        cy.url().should('include', '/seller/dashboard')
        // Verify seller navigation items
        cy.get('nav').should('contain', 'Dashboard')
        cy.get('nav').should('contain', 'Product')
      })
    })

    describe('Role Switching', () => {
      it('should switch from buyer to seller', () => {
        // Find the buyer button by its text and click it
        cy.contains('button', "I'm a Buyer").should('be.visible').click()
        cy.url().should('include', '/productlisting')

        // Go back to role selection
        cy.visit('/dashboard1')
        cy.get('div.bg-white').should('be.visible')

        // Switch to seller
        cy.contains('button', "I'm a Seller").should('be.visible').click()
        cy.url().should('include', '/seller/dashboard')
      })

      it('should switch from seller to buyer', () => {
        // Find the seller button by its text and click it
        cy.contains('button', "I'm a Seller").should('be.visible').click()
        cy.url().should('include', '/seller/dashboard')

        // Go back to role selection
        cy.visit('/dashboard1')
        cy.get('div.bg-white').should('be.visible')

        // Switch to buyer
        cy.contains('button', "I'm a Buyer").should('be.visible').click()
        cy.url().should('include', '/productlisting')
      })
    })

    describe('Role Access Restrictions', () => {
      it('should restrict buyer from accessing admin pages', () => {
        // Select buyer role
        cy.contains('button', "I'm a Buyer").should('be.visible').click()
        cy.url().should('include', '/productlisting')
        
        // Try to access admin dashboard and expect a 403
        cy.request({
          url: '/admin/dashboard',
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).to.eq(403)
        })
      })

      it('should restrict seller from accessing admin pages', () => {
        // Select seller role
        cy.contains('button', "I'm a Seller").should('be.visible').click()
        cy.url().should('include', '/seller/dashboard')
        
        // Try to access admin dashboard and expect a 403
        cy.request({
          url: '/admin/dashboard',
          failOnStatusCode: false
        }).then((response) => {
          expect(response.status).to.eq(403)
        })
      })
    })
  })
})
