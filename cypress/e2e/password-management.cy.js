describe('Password Management', () => {
  beforeEach(() => {
    // Register a test user
    const timestamp = new Date().getTime()
    const email = `test${timestamp}@example.com`
    const password = 'Password123!'

    cy.visit('/register')
    cy.get('form[action*="register"]').should('be.visible').within(() => {
      cy.get('#name').should('be.visible').type('Test User')
      cy.get('#email').should('be.visible').type(email)
      cy.get('#location').should('be.visible').select('Colombo')
      cy.get('#password').should('be.visible').type(password)
      cy.get('#password_confirmation').should('be.visible').type(password)
    })
    cy.get('form[action*="register"]').submit()

    // Verify registration was successful and we're on dashboard
    cy.url().should('include', '/dashboard')

    // Store email and password for later use
    cy.wrap(email).as('userEmail')
    cy.wrap(password).as('userPassword')
  })

  describe('Password Reset Request', () => {
    beforeEach(() => {
      // First ensure we're logged in and on dashboard
      cy.url().should('include', '/dashboard')
      
      // Wait for the navbar to be visible
      cy.get('nav').should('be.visible')

      // Click on the Settings Dropdown button (contains user name)
      cy.get('.sm\\:flex.sm\\:items-center.sm\\:ms-6 button').click()
      
      // Click logout from dropdown
      cy.contains('Log Out').click()
      
      // Verify we're not on dashboard
      cy.url().should('not.include', '/dashboard')
      
      // Wait for the navbar and click login link
      cy.get('nav').should('be.visible')
      cy.contains('login').click()
      
      // Verify we're on login page
      cy.url().should('include', '/login')
      
      // Wait for login page to load and click forgot password
      cy.contains('Forgot your password?').click()
      
      // Verify we're on the forgot password page
      cy.url().should('include', '/forgot-password')
      cy.get('form[action*="forgot-password"]').should('be.visible')
    })

    it('should send reset link for valid email', () => {
      cy.get('@userEmail').then(email => {
        cy.get('form[action*="forgot-password"]').within(() => {
          cy.get('#email').should('be.visible').type(email)
        })
        cy.get('form[action*="forgot-password"]').submit()
        cy.get('.text-sm.text-green-600').should('be.visible')
          .and('contain', 'password reset link')
      })
    })

    it('should show error for invalid email format', () => {
      cy.get('form[action*="forgot-password"]').within(() => {
        cy.get('#email').should('be.visible').type('invalid-email')
      })
      cy.get('form[action*="forgot-password"]').submit()
      cy.get('.text-sm.text-red-600').should('be.visible')
        .and('contain', 'valid email')
    })

    it('should show error for non-existent email', () => {
      cy.get('form[action*="forgot-password"]').within(() => {
        cy.get('#email').should('be.visible').type('nonexistent@example.com')
      })
      cy.get('form[action*="forgot-password"]').submit()
      cy.get('.text-sm.text-red-600').should('be.visible')
        .and('contain', 'find a user')
    })
  })

  describe('Password Change', () => {
    beforeEach(() => {
      // First ensure we're logged in and on dashboard
      cy.url().should('include', '/dashboard')
      
      // Wait for the navbar to be visible
      cy.get('nav').should('be.visible')

      // Click on the user name dropdown
      cy.get('.sm\\:flex.sm\\:items-center.sm\\:ms-6 button').click()
      
      // Click Profile from dropdown
      cy.get('a[href*="profile"]').contains('Profile').click()
      
      // Wait for profile page to load
      cy.url().should('include', '/profile')
      
      // Wait for Update Password heading
      cy.contains('h2', 'Update Password').should('be.visible')
    })

    it('should change password with valid current password', () => {
      cy.get('@userPassword').then(currentPassword => {
        // Fill in the password form
        cy.get('#update_password_current_password').type(currentPassword)
        cy.get('#update_password_password').type('NewPassword123!')
        cy.get('#update_password_password_confirmation').type('NewPassword123!')
        
        // Find the section with Update Password heading and click its Save button
        cy.contains('h2', 'Update Password')
          .parent('header')
          .parent('section')
          .find('button')
          .contains('Save')
          .click()
        
        // Check for success message
        cy.contains('.text-sm.text-gray-600', 'Saved').should('be.visible')
      })
    })

    it('should show error for incorrect current password', () => {
      // Fill in the password form with wrong current password
      cy.get('#update_password_current_password').type('WrongPassword123!')
      cy.get('#update_password_password').type('NewPassword123!')
      cy.get('#update_password_password_confirmation').type('NewPassword123!')
      
      // Find the section with Update Password heading and click its Save button
      cy.contains('h2', 'Update Password')
        .parent('header')
        .parent('section')
        .find('button')
        .contains('Save')
        .click()
      
      // Wait for error message and check it
      cy.get('#update_password_current_password')
        .parent()
        .find('.text-sm.text-red-600.dark\\:text-red-400.space-y-1.mt-2')
        .should('be.visible')
        .and('contain', 'incorrect')
    })

    it('should enforce password complexity requirements', () => {
      cy.get('@userPassword').then(currentPassword => {
        // Fill in the password form with weak password
        cy.get('#update_password_current_password').type(currentPassword)
        cy.get('#update_password_password').type('weak')
        cy.get('#update_password_password_confirmation').type('weak')
        
        // Find the section with Update Password heading and click its Save button
        cy.contains('h2', 'Update Password')
          .parent('header')
          .parent('section')
          .find('button')
          .contains('Save')
          .click()
        
        // Wait for error message and check it
        cy.get('#update_password_password')
          .parent()
          .find('.text-sm.text-red-600.dark\\:text-red-400.space-y-1.mt-2')
          .should('be.visible')
          .and('contain', 'must be at least')
      })
    })

    it('should require password confirmation to match', () => {
      cy.get('@userPassword').then(currentPassword => {
        // Fill in the password form with mismatched passwords
        cy.get('#update_password_current_password').type(currentPassword)
        cy.get('#update_password_password').type('NewPassword123!')
        cy.get('#update_password_password_confirmation').type('DifferentPassword123!')
        
        // Find the section with Update Password heading and click its Save button
        cy.contains('h2', 'Update Password')
          .parent('header')
          .parent('section')
          .find('button')
          .contains('Save')
          .click()
        
        // Wait for error message and check it
        cy.get('#update_password_password')
          .parent()
          .find('.text-sm.text-red-600.dark\\:text-red-400.space-y-1.mt-2')
          .should('be.visible')
          .and('contain', 'confirmation')
      })
    })
  })
})
