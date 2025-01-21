describe('Admin Rating Management', () => {
  const adminEmail = 'admin@test.com'
  const adminPassword = 'password123'

  beforeEach(() => {
    cy.viewport(1280, 720)
    cy.visit('/login')
    cy.get('input[name="email"]').type(adminEmail)
    cy.get('input[name="password"]').type(adminPassword)
    cy.get('button[type="submit"]').click()
    cy.visit('/admin/ratings')
  })

  it('Admin can view all rating details', () => {
    cy.get('.rating-details').first().within(() => {
      // Check all rating information is visible
      cy.get('.rating-stars').should('exist')
      cy.get('.rating-comment').should('exist')
      cy.get('.buyer-info').should('exist')
      cy.get('.product-info').should('exist')
      cy.get('.seller-info').should('exist')
      cy.get('.order-info').should('exist')
    })
  })

  it('Admin can approve ratings', () => {
    cy.get('.rating-details').contains('Pending Approval').parent().within(() => {
      cy.get('button').contains('Approve').click()
      cy.contains('Rating approved successfully')
    })
  })

  it('Admin can reject ratings with reason', () => {
    cy.get('.rating-details').contains('Pending Approval').parent().within(() => {
      cy.get('button').contains('Reject').click()
    })

    // Fill rejection reason
    cy.get('textarea[name="rejection_reason"]').type('Inappropriate content')
    cy.get('button').contains('Confirm Rejection').click()
    cy.contains('Rating rejected successfully')
  })

  it('Admin can filter ratings', () => {
    // Test status filter
    cy.get('select[name="status"]').select('approved')
    cy.get('.rating-details').each(($el) => {
      cy.wrap($el).should('contain', 'Approved')
    })

    // Test star rating filter
    cy.get('select[name="rating"]').select('5')
    cy.get('.rating-details').each(($el) => {
      cy.wrap($el).find('.rating-stars').should('contain', '5')
    })
  })

  it('Admin can search ratings', () => {
    // Search by product name
    cy.get('input[name="search"]').type('Test Product')
    cy.get('.rating-details').should('contain', 'Test Product')

    // Search by seller name
    cy.get('input[name="search"]').clear().type('Test Seller')
    cy.get('.rating-details').should('contain', 'Test Seller')
  })

  it('Admin can view rating statistics', () => {
    cy.get('.rating-stats').within(() => {
      cy.get('.total-ratings').should('exist')
      cy.get('.average-rating').should('exist')
      cy.get('.rating-distribution').should('exist')
    })
  })

  it('Admin can export ratings', () => {
    cy.get('button').contains('Export Ratings').click()
    cy.readFile('cypress/downloads/ratings-export.csv').should('exist')
  })

  it('Admin can bulk approve ratings', () => {
    // Select multiple ratings
    cy.get('.rating-checkbox').first().check()
    cy.get('.rating-checkbox').eq(1).check()
    
    // Bulk approve
    cy.get('button').contains('Bulk Approve').click()
    cy.contains('Selected ratings approved successfully')
  })
})
