describe('Rating System Validations', () => {
  const buyerEmail = 'buyer@test.com'
  const buyerPassword = 'password123'

  beforeEach(() => {
    cy.viewport(1280, 720)
    cy.visit('/login')
    cy.get('input[name="email"]').type(buyerEmail)
    cy.get('input[name="password"]').type(buyerPassword)
    cy.get('button[type="submit"]').click()
    cy.visit('/orders')
  })

  it('Cannot rate before delivery is confirmed', () => {
    // Find an order that's not delivered
    cy.get('tr').contains('Pending').parent('tr').within(() => {
      cy.get('button').contains('Leave Rating').should('not.exist')
    })
  })

  it('Cannot rate twice', () => {
    // Find an order that's already rated
    cy.get('tr').contains('Completed').parent('tr').within(() => {
      cy.get('.rating-given').should('exist')
      cy.get('button').contains('Leave Rating').should('not.exist')
    })
  })

  it('Rating requires minimum characters for comment', () => {
    // Find a completed order without rating
    cy.get('tr').contains('Completed').parent('tr').within(() => {
      cy.get('.rating-given').should('not.exist')
      cy.get('button').contains('Leave Rating').click()
    })

    // Try to submit with short comment
    cy.get('textarea[name="comment"]').type('OK')
    cy.get('button').contains('Submit Rating').click()
    cy.contains('Comment must be at least 10 characters')
  })

  it('Rating requires star selection', () => {
    // Find a completed order without rating
    cy.get('tr').contains('Completed').parent('tr').within(() => {
      cy.get('.rating-given').should('not.exist')
      cy.get('button').contains('Leave Rating').click()
    })

    // Try to submit without selecting stars
    cy.get('textarea[name="comment"]').type('This is a good product!')
    cy.get('button').contains('Submit Rating').click()
    cy.contains('Please select a rating')
  })

  it('Shows correct rating options', () => {
    // Find a completed order without rating
    cy.get('tr').contains('Completed').parent('tr').within(() => {
      cy.get('.rating-given').should('not.exist')
      cy.get('button').contains('Leave Rating').click()
    })

    // Verify rating options
    cy.get('.star-rating button').should('have.length', 5)
    cy.get('input[name="is_anonymous"]').should('exist')
    cy.get('textarea[name="comment"]').should('exist')
  })

  it('Preserves anonymity when selected', () => {
    // Find a completed order without rating
    cy.get('tr').contains('Completed').parent('tr').within(() => {
      cy.get('.rating-given').should('not.exist')
      cy.get('button').contains('Leave Rating').click()
    })

    // Submit anonymous rating
    cy.get('.star-rating').find('button').eq(4).click()
    cy.get('textarea[name="comment"]').type('This is an anonymous review!')
    cy.get('input[name="is_anonymous"]').check()
    cy.get('button').contains('Submit Rating').click()

    // Verify anonymity in order details
    cy.visit('/orders')
    cy.get('tr').first().find('a').contains('View Details').click()
    cy.get('.rating-section').should('contain', 'Anonymous')
  })
})
