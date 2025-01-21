describe('Order Ratings and Reviews Flow', () => {
  beforeEach(() => {
    // Prevent Cypress from failing on uncaught exceptions
    Cypress.on('uncaught:exception', (err, runnable) => {
      return false
    })
  })

  it('Buyer leaves rating and review for an order', () => {
    // Step 1: Log in as buyer
    cy.visit('http://13.53.94.73/login')
    cy.get('input[name="email"]').type('az@gmail.com')
    cy.get('input[name="password"]').type('12345678')
    cy.get('button[type="submit"]').click()
    cy.url().should('include', '/dashboard1')

    // Step 2: Go to orders page
    cy.visit('http://13.53.94.73/orders')
    cy.contains('Order History').should('be.visible')

    // Step 3: Find first order and click "Leave Rating"
    cy.contains('button', 'Leave Rating').first().click()

    // Step 4: Fill in rating details
    cy.get('.rating-form').within(() => {
      // Select 5 stars
      cy.get('.star-rating button').last().click()
      
      // Add comment
      cy.get('textarea[name="comment"]').type('Great product and excellent service!')
      
      // Make it anonymous
      cy.get('input[name="is_anonymous"]').check()
      
      // Submit rating
      cy.contains('button', 'Submit Rating').click()
    })

    // Step 5: Verify success message
    cy.get('.bg-green-500').should('be.visible')
    cy.contains('Rating submitted successfully').should('be.visible')

    // Step 6: Logout as buyer
    cy.get('button').contains('az').click()
    cy.contains('Log Out').click()
  })

  it('Seller views the rating', () => {
    // Step 1: Login as seller
    cy.visit('http://13.53.94.73/login')
    cy.get('input[name="email"]').type('brittni@gmail.com')
    cy.get('input[name="password"]').type('brittni123')
    cy.get('button[type="submit"]').click()

    // Step 2: Go to seller ratings page
    cy.visit('http://13.53.94.73/seller/ratings')

    // Step 3: Verify rating appears
    cy.get('.rating-card').first().within(() => {
      cy.get('.stars').should('contain', '5')
      cy.get('.comment').should('contain', 'Anonymous') // Since rating was anonymous
      cy.contains('Great product and excellent service!').should('be.visible')
    })

    // Step 4: Logout as seller
    cy.get('button').contains('brittni').click()
    cy.contains('Log Out').click()
  })

  it('Admin reviews the rating', () => {
    // Step 1: Login as admin
    cy.visit('http://13.53.94.73/login')
    cy.get('input[name="email"]').type('admin@gmail.com')
    cy.get('input[name="password"]').type('admin123')
    cy.get('button[type="submit"]').click()

    // Step 2: Visit admin ratings page
    cy.visit('http://13.53.94.73/admin/ratings')

    // Step 3: Verify rating details
    cy.get('.rating-details').first().within(() => {
      cy.get('.stars').should('contain', '5')
      cy.get('.user-info').should('contain', 'Anonymous')
      cy.get('.comment').should('contain', 'Great product and excellent service!')
      cy.get('.product-info').should('be.visible')
      cy.get('.order-info').should('be.visible')
    })

    // Step 4: Logout as admin
    cy.get('[data-dropdown-toggle="dropdown-user"]').click()
    cy.contains('Log Out').click()
  })
})