describe('Authentication Tests', () => {
  // Registration Tests
  describe('User Registration', () => {
    beforeEach(() => {
      // Wait for page to load completely
      cy.visit('/register')
      cy.get('form').should('be.visible')
    })

    it('should register with valid credentials', () => {
      const timestamp = new Date().getTime();
      // Wait for form elements to be available
      cy.get('form').within(() => {
        cy.get('#name').should('be.visible').type('John Doe')
        cy.get('#email').should('be.visible').type(`johndoe${timestamp}@example.com`)
        cy.get('#location').should('be.visible').select('Colombo')
        cy.get('#password').should('be.visible').type('Password123!')
        cy.get('#password_confirmation').should('be.visible').type('Password123!')
      })
      cy.get('form').submit()
      // Wait for redirect and check for success indicators
      cy.url().should('not.include', '/register')
      cy.get('.text-sm.text-red-600').should('not.exist')
    })

    it('should show error for invalid email format', () => {
      cy.get('form').within(() => {
        cy.get('#name').should('be.visible').type('John Doe')
        cy.get('#email').should('be.visible').type('invalidemail')
        cy.get('#location').should('be.visible').select('Colombo')
        cy.get('#password').should('be.visible').type('Password123!')
        cy.get('#password_confirmation').should('be.visible').type('Password123!')
      })
      cy.get('form').submit()
      cy.get('.text-sm.text-red-600').should('be.visible')
    })

    it('should show error for short password', () => {
      cy.get('form').within(() => {
        cy.get('#name').should('be.visible').type('John Doe')
        cy.get('#email').should('be.visible').type('test@example.com')
        cy.get('#location').should('be.visible').select('Colombo')
        cy.get('#password').should('be.visible').type('short')
        cy.get('#password_confirmation').should('be.visible').type('short')
      })
      cy.get('form').submit()
      cy.get('.text-sm.text-red-600').should('be.visible')
    })

    it('should show error for mismatched passwords', () => {
      cy.get('form').within(() => {
        cy.get('#name').should('be.visible').type('John Doe')
        cy.get('#email').should('be.visible').type('test@example.com')
        cy.get('#location').should('be.visible').select('Colombo')
        cy.get('#password').should('be.visible').type('Password123!')
        cy.get('#password_confirmation').should('be.visible').type('DifferentPass123!')
      })
      cy.get('form').submit()
      cy.get('.text-sm.text-red-600').should('be.visible')
    })

    it('should show error for existing email', () => {
      // First register a user
      cy.get('form').within(() => {
        cy.get('#name').should('be.visible').type('John Doe')
        cy.get('#email').should('be.visible').type('existing@example.com')
        cy.get('#location').should('be.visible').select('Colombo')
        cy.get('#password').should('be.visible').type('Password123!')
        cy.get('#password_confirmation').should('be.visible').type('Password123!')
      })
      cy.get('form').submit()

      // Try to register again with same email
      cy.visit('/register')
      cy.get('form').should('be.visible').within(() => {
        cy.get('#name').should('be.visible').type('Jane Doe')
        cy.get('#email').should('be.visible').type('existing@example.com')
        cy.get('#location').should('be.visible').select('Colombo')
        cy.get('#password').should('be.visible').type('Password123!')
        cy.get('#password_confirmation').should('be.visible').type('Password123!')
      })
      cy.get('form').submit()
      cy.get('.text-sm.text-red-600').should('be.visible')
    })

    it('should show error for empty required fields', () => {
      cy.get('form').submit()
      cy.get('.text-sm.text-red-600').should('be.visible')
    })

    it('should test all location options', () => {
      const locations = [
        'Ampara', 'Anuradhapura', 'Badulla', 'Batticaloa', 'Colombo',
        'Galle', 'Gampaha', 'Hambantota', 'Jaffna', 'Kalutara',
        'Kandy', 'Kegalle', 'Kilinochchi', 'Kurunegala', 'Mannar',
        'Matale', 'Matara', 'Monaragala', 'Mullaitivu', 'Nuwara Eliya',
        'Polonnaruwa', 'Puttalam', 'Ratnapura', 'Trincomalee', 'Vavuniya'
      ]
      
      locations.forEach(location => {
        cy.get('#location').should('be.visible').select(location)
        cy.get('#location').should('have.value', location)
      })
    })
  })

  // Login Tests
  describe('User Login', () => {
    beforeEach(() => {
      // First ensure we have a registered user
      cy.visit('/register')
      cy.get('form').should('be.visible').within(() => {
        cy.get('#name').should('be.visible').type('Test User')
        cy.get('#email').should('be.visible').type('user@example.com')
        cy.get('#location').should('be.visible').select('Colombo')
        cy.get('#password').should('be.visible').type('Password123!')
        cy.get('#password_confirmation').should('be.visible').type('Password123!')
      })
      cy.get('form').submit()
      
      // Now visit login page and wait for it to load
      cy.visit('/login')
      cy.get('form').should('be.visible')
    })

    it('should login with valid credentials', () => {
      cy.get('form').within(() => {
        cy.get('#email').should('be.visible').type('user@example.com')
        cy.get('#password').should('be.visible').type('Password123!')
      })
      cy.get('form').submit()
      // Wait for redirect and check for success indicators
      cy.url().should('not.include', '/login')
      cy.get('.text-sm.text-red-600').should('not.exist')
    })

    it('should show error for incorrect password', () => {
      cy.get('form').within(() => {
        cy.get('#email').should('be.visible').type('user@example.com')
        cy.get('#password').should('be.visible').type('WrongPassword123!')
      })
      cy.get('form').submit()
      cy.get('.text-sm.text-red-600').should('be.visible')
    })

    it('should show error for non-existent email', () => {
      cy.get('form').within(() => {
        cy.get('#email').should('be.visible').type('nonexistent@example.com')
        cy.get('#password').should('be.visible').type('Password123!')
      })
      cy.get('form').submit()
      cy.get('.text-sm.text-red-600').should('be.visible')
    })

    it('should remember user session when "Remember Me" is checked', () => {
      cy.get('form').within(() => {
        cy.get('#email').should('be.visible').type('user@example.com')
        cy.get('#password').should('be.visible').type('Password123!')
        cy.get('#remember_me').should('be.visible').check()
      })
      cy.get('form').submit()

      // Wait for successful login
      cy.url().should('not.include', '/login')
      
      // Visit login page again - should be redirected if remembered
      cy.visit('/login')
      cy.url().should('not.include', '/login')
    })

    // it('should handle dark mode form elements', () => {
    //   cy.get('form').within(() => {
    //     // Verify dark mode classes are present
    //     cy.get('#email').should('have.class', 'dark:bg-gray-700')
    //     cy.get('#password').should('have.class', 'dark:bg-gray-700')
    //     cy.get('#remember_me').should('have.class', 'dark:bg-gray-700')
    //   })
    // })

    it('should show validation errors in proper styling', () => {
      cy.get('form').submit() // Submit empty form
      cy.get('.text-sm.text-red-600').should('be.visible')
    })
  })
})
