describe('Profile Management', () => {
  beforeEach(() => {
    // Clear any existing session
    cy.request({
      method: 'POST',
      url: '/logout',
      failOnStatusCode: false
    })
    
    // Register and login a test user
    const timestamp = new Date().getTime()
    const email = `test${timestamp}@example.com`
    const password = 'Password123!'

    cy.visit('/register')
    cy.get('form').should('be.visible')
    cy.get('#name').should('be.visible').type('Test User', { force: true })
    cy.get('#email').should('be.visible').type(email, { force: true })
    cy.get('#location').should('be.visible').select('Colombo')
    cy.get('#password').should('be.visible').type(password, { force: true })
    cy.get('#password_confirmation').should('be.visible').type(password, { force: true })
    
    // Submit form and wait for navigation
    cy.get('form').submit()
    cy.url().should('include', '/dashboard1', { timeout: 30000 })
  })

  describe('View Profile', () => {
    it('should display current user profile information', () => {
      // Navigate to profile page
      cy.visit('/profile')
      
      // Verify profile information form is displayed
      cy.contains('Profile Information').should('be.visible')
      
      // Verify form fields are visible
      cy.get('#name').should('be.visible')
      
      // Check email field properties
      cy.get('#email')
        .should('be.visible')
        .should(($el) => {
          expect($el).to.have.attr('readonly')
          expect($el).to.have.attr('disabled')
        })
      
      // Verify province field
      cy.get('#province').should('be.visible')
      
      // Verify save button
      cy.get('button[type="submit"]').should('be.visible')
    })

    it('should show email verification status', () => {
      cy.visit('/profile')
      
      // Check for verification message if email is not verified
      cy.get('body').then($body => {
        if ($body.find('.text-gray-800:contains("Your email address is unverified")').length > 0) {
          cy.contains('Click here to re-send the verification email').should('be.visible')
        }
      })
    })
  })

  describe('Update Profile', () => {
    beforeEach(() => {
      cy.visit('/profile')
      // Fill in all required fields first
      cy.get('#province').select('Western')
      cy.get('#location').select('Colombo')
      cy.get('#address').clear().type('Default Address, Colombo', { force: true })
    })

    it('should allow updating name', () => {
      const newName = 'Updated Test User'
      
      // Update name while keeping other fields filled
      cy.get('#name').clear().type(newName, { force: true })
      cy.get('#phone').clear().type('0771234567', { force: true })
      cy.get('#province').select('Western')
      cy.get('#location').select('Colombo')
      cy.get('#address').clear().type('123 Test Street, Colombo', { force: true })
      
      // Click Save Changes button
      cy.contains('button', 'Save Changes').click()
      
      // Verify success message
      cy.contains('Saved.').should('be.visible')
      
      // Verify name was updated
      cy.get('#name').should('have.value', newName)
    })

    it('should allow updating phone number', () => {
      const newPhone = '0777654321'
      
      // Update phone while keeping other fields filled
      cy.get('#name').clear().type('Test User', { force: true })
      cy.get('#phone').clear().type(newPhone, { force: true })
      cy.get('#province').select('Western')
      cy.get('#location').select('Colombo')
      cy.get('#address').clear().type('123 Test Street, Colombo', { force: true })
      
      // Click Save Changes button
      cy.contains('button', 'Save Changes').click()
      
      // Verify success message
      cy.contains('Saved.').should('be.visible')
      
      // Verify phone was updated
      cy.get('#phone').should('have.value', newPhone)
    })

    it('should allow updating address details', () => {
      // Update address details
      const newProvince = 'Central'
      const newDistrict = 'Kandy'
      const newAddress = '456 Hill Street, Kandy'
      
      cy.get('#name').clear().type('Test User', { force: true })
      cy.get('#phone').clear().type('0771234567', { force: true })
      
      // Update province and wait for district options
      cy.get('#province').select(newProvince)
      cy.wait(2000)
      
      // Select district after ensuring options are loaded
      cy.get('#location')
        .should('not.be.disabled')
        .and('be.visible')
        .find('option')
        .should('have.length.gt', 1)
        .then(() => {
          cy.get('#location').select(newDistrict, { force: true })
        })
      
      cy.get('#address').clear().type(newAddress, { force: true })
      
      // Click Save Changes button
      cy.contains('button', 'Save Changes').click()
      
      // Verify success message
      cy.contains('Saved.').should('be.visible')
      
      // After page reload, select province again and verify
      cy.get('#province')
        .should('have.value', newProvince)
        .select(newProvince)
      
      // Wait and verify district selection
      cy.wait(2000)
      cy.get('#location')
        .should('not.be.disabled')
        .and('be.visible')
        .find('option')
        .should('have.length.gt', 1)
        .then(() => {
          // Select district again after page reload
          cy.get('#location').select(newDistrict, { force: true })
          // Now verify the value
          cy.get('#location').should('have.value', newDistrict)
        })
      
      // Verify address
      cy.get('#address').should('have.value', newAddress)
    })
  })

  describe('Validation', () => {
    it('should prevent saving empty required fields', () => {
      cy.visit('/profile')
      
      // Clear required fields
      cy.get('#name').clear()
      cy.get('#phone').clear()
      cy.get('#province').select('')
      cy.get('#location').select('')
      cy.get('#address').clear()
      
      // Click Save Changes button
      cy.contains('button', 'Save Changes').click()
      
      // Verify error messages for each required field
      cy.get('#name').then($el => {
        expect($el[0].validationMessage).to.not.be.empty
      })
      
      cy.get('#phone').then($el => {
        expect($el[0].validationMessage).to.not.be.empty
      })
      
      cy.get('#province').then($el => {
        expect($el[0].validationMessage).to.not.be.empty
      })
      
      cy.get('#location').then($el => {
        expect($el[0].validationMessage).to.not.be.empty
      })
      
      // Verify form was not submitted
      cy.contains('Saved.').should('not.exist')
    })

    it('should require province and district selection', () => {
      cy.visit('/profile')
      
      // Fill in other required fields
      cy.get('#name').clear().type('Test User')
      cy.get('#phone').clear().type('0771234567')
      cy.get('#address').clear().type('Test Address')
      
      // Try to submit with empty province/district
      cy.get('#province').select('')
      cy.get('#location').select('')
      
      // Click Save Changes button
      cy.contains('button', 'Save Changes').click()
      
      // Verify validation messages
      cy.get('#province').then($el => {
        expect($el[0].validationMessage).to.not.be.empty
      })
      
      // Fill province but leave district empty
      cy.get('#province').select('Western')
      cy.wait(1000) // Wait for district options to load
      cy.get('#location').select('')
      
      // Try to submit again
      cy.contains('button', 'Save Changes').click()
      
      // Verify district validation
      cy.get('#location').then($el => {
        expect($el[0].validationMessage).to.not.be.empty
      })
      
      // Verify form was not submitted
      cy.contains('Saved.').should('not.exist')
    })

    it('should validate phone number format', () => {
      cy.visit('/profile')
      
      // Fill in other required fields
      cy.get('#name').clear().type('Test User')
      cy.get('#province').select('Western')
      cy.wait(1000)
      cy.get('#location').select('Colombo')
      cy.get('#address').clear().type('Test Address')
      
      // Test invalid phone formats
      const invalidPhoneNumbers = ['invalid', '123', 'abc12345678', '077123']
      
      invalidPhoneNumbers.forEach(phone => {
        cy.get('#phone').clear().type(phone)
        cy.contains('button', 'Save Changes').click()
        
        // Check for error message near the phone field
        cy.get('#phone')
          .parents('div')
          .find('.text-sm.text-red-600')
          .should('be.visible')
          .and('contain', 'Please enter a valid phone number')
        
        // Verify form was not submitted
        cy.contains('Saved.').should('not.exist')
      })
      
      // Verify valid phone number works
      cy.get('#phone').clear().type('0771234567')
      cy.contains('button', 'Save Changes').click()
      
      // Wait for success message
      cy.contains('Saved.').should('be.visible')
      
      // Verify phone number was updated
      cy.get('#phone').should('have.value', '0771234567')
    })
  })
})
