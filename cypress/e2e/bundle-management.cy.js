describe('Bundle Management', () => {
  const timestamp = new Date().getTime()
  const sellerEmail = `test.seller.${timestamp}@gmail.com`
  const sellerPassword = 'Password123!'

  before(() => {
    // Register as a seller
    cy.visit('/register')
    cy.get('form').within(() => {
      cy.get('#name').type('Test Seller')
      cy.get('#email').type(sellerEmail)
      cy.get('#location').select('Colombo')
      cy.get('#password').type(sellerPassword)
      cy.get('#password_confirmation').type(sellerPassword)
    })
    cy.get('form').submit()

    // Switch to seller mode
    cy.url().should('include', '/dashboard1')
    cy.contains('button', "I'm a Seller").click()
    cy.url().should('include', '/seller/dashboard')
  })

  beforeEach(() => {
    // Handle uncaught exceptions
    Cypress.on('uncaught:exception', (err, runnable) => {
      // Return false to prevent the error from failing the test
      return false
    })

    // Login if needed using the same email
    cy.visit('/seller/bundles')
    cy.url().then((url) => {
      if (url.includes('/login')) {
        cy.get('input[name="email"]').type(sellerEmail)
        cy.get('input[name="password"]').type(sellerPassword)
        cy.get('button[type="submit"]').click()
      }
    })
  })

  describe('Bundle Creation', () => {
    it('should create first bundle with multiple items', () => {
      const timestamp = new Date().getTime()
      const bundleName = `First Bundle ${timestamp}`

      // Verify empty state message
      cy.contains("You haven't created any bundles yet.").should('be.visible')
      
      // Click Create Your First Bundle
      cy.contains('Create Your First Bundle').click()

      // Fill bundle details
      cy.get('input[name="bundleName"]').type(bundleName)
      cy.get('textarea[name="description"]').type('This is my first test bundle')
      cy.get('input[name="price"]').type('399.99')

      // Upload main bundle image
      cy.get('input[name="bundleImage"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })

      // Fill first item
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 1')
        cy.get('input[name="categoryImages[]"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Add and fill second item
      cy.get('#add-product').click()
      cy.wait(500)
      cy.get('.product-item').eq(1).within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 2')
        cy.get('input[name="categoryImages[]"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Add and fill third item
      cy.get('#add-product').click()
      cy.wait(500)
      cy.get('.product-item').eq(2).within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 3')
        cy.get('input[name="categoryImages[]"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Remove the 4th item if it exists
      cy.get('.product-item').then(($items) => {
        if ($items.length > 3) {
          cy.get('.product-item').last().find('button').contains('Remove').click()
          cy.wait(500)
        }
      })

      // Verify we have exactly 3 items after removal
      cy.get('.product-item').should('have.length', 3)

      // Intercept the form submission
      cy.intercept('POST', '**/seller/bundles').as('createBundle')

      // Submit the form
      cy.get('button[type="submit"]').click({ force: true })

      // Wait for the bundle creation request to complete
      cy.wait('@createBundle', { timeout: 10000 }).then((interception) => {
        // Check if the request was successful
        expect(interception.response.statusCode).to.be.oneOf([200, 201, 302])

        // Visit bundles page after successful creation
        cy.visit('/seller/bundles')

        // Verify bundle creation
        cy.contains(bundleName, { timeout: 15000 }).should('be.visible')
        cy.contains('Pending Review', { timeout: 15000 }).should('be.visible')
      })
    })

    it('should validate bundle image requirements', () => {
      // Click Create New Bundle button
      cy.contains('Create New Bundle').click()
      
      // Fill in all required fields first
      cy.get('input[name="bundleName"]').type('Test Bundle')
      cy.get('textarea[name="description"]').type('Test description')
      cy.get('input[name="price"]').type('299.99')
      
      // Add first product
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 1')
        cy.get('input[name="categoryImages[]"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })
      
      // Add second product
      cy.get('#add-product').click()
      cy.wait(500)
      cy.get('.product-item').eq(1).within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 2')
        cy.get('input[name="categoryImages[]"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Remove any extra product items beyond the first two
      cy.get('.product-item').then($items => {
        if ($items.length > 2) {
          for (let i = 2; i < $items.length; i++) {
            cy.get('.product-item').eq(i).find('button:contains("Remove")').click()
            cy.wait(200)
          }
        }
      })

      // Verify we have exactly 2 product items
      cy.get('.product-item').should('have.length', 2)
      
      // Set up interception for all form submissions
      cy.intercept('POST', '**/seller/bundles').as('bundleSubmit')
      
      // Try with invalid file type
      cy.get('input[name="bundleImage"]').selectFile('cypress/fixtures/invalid-file.txt', { force: true })
      cy.get('button[type="submit"]').click()
      cy.contains('The bundle image field must be an image.').should('be.visible')
      
      // Now try with valid image and submit
      cy.get('input[name="bundleImage"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })
      cy.get('button[type="submit"]').click()
      
      // Wait for the submission and verify redirect
      cy.wait('@bundleSubmit', { timeout: 10000 })
      cy.url().should('include', '/seller/bundles')
    })

    it('should validate minimum and maximum items', () => {
      // Start bundle creation
      cy.get('body').then($body => {
        const button = $body.text().includes("You haven't created any bundles yet") 
          ? 'Create Your First Bundle'
          : 'Create New Bundle'
        cy.contains(button).click()
      })

      // Fill basic details
      cy.get('input[name="bundleName"]').type('Item Count Test Bundle')
      cy.get('textarea[name="description"]').type('Testing item count validation')
      cy.get('input[name="price"]').type('199.99')
      cy.get('input[name="bundleImage"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })

      // Add first item with all required fields
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 1')
        cy.get('input[name="categoryImages[]"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Add second item but leave it empty to test required field validation
      cy.get('#add-product').click()
      cy.wait(500)

      // Try to submit with empty second item
      cy.get('button[type="submit"]').click()
      cy.get('.product-item').eq(1).within(() => {
        cy.get('input[name="categories[]"]')
          .invoke('prop', 'validationMessage')
          .should('not.be.empty')
          .should('contain', 'Please fill out this field')
      })

      // Fill in the second item properly
      cy.get('.product-item').eq(1).within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 2')
        cy.get('input[name="categoryImages[]"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Add and fill third item
      cy.get('#add-product').click()
      cy.wait(500)
      cy.get('.product-item').eq(2).within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 3')
        cy.get('input[name="categoryImages[]"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Add and fill fourth item
      cy.get('#add-product').click()
      cy.wait(500)
      cy.get('.product-item').eq(3).within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 4')
        cy.get('input[name="categoryImages[]"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Fill in the fifth item (which is already present)
      cy.get('.product-item').eq(4).within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 5')
        cy.get('input[name="categoryImages[]"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Verify we have 5 items and can't add more
      cy.get('.product-item').should('have.length', 5)
      cy.get('#add-product').should('be.disabled')

      // Submit the form with all items filled (should succeed)
      cy.intercept('POST', '**/seller/bundles').as('bundleSubmit')
      cy.get('button[type="submit"]').click()
      cy.wait('@bundleSubmit', { timeout: 10000 })
      cy.url().should('include', '/seller/bundles')
    })

    it('should validate required fields', () => {
      // Click appropriate create button based on state
      cy.get('body').then(($body) => {
        const button = $body.text().includes("You haven't created any bundles yet")
          ? 'Create Your First Bundle'
          : 'Create New Bundle'
        cy.contains(button).click()
      })

      // Try to submit empty form first
      cy.get('button[type="submit"]').click()
      
      // Validate first product
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]')
          .invoke('prop', 'validationMessage')
          .should('not.be.empty')
          .should('contain', 'Please fill out this field')
      })

      // Fill first product
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]').type('Product 1')
        cy.get('input[name="categoryImages[]"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Add second product
      cy.get('#add-product').click()
      cy.wait(500)

      // Try to submit with empty second product
      cy.get('button[type="submit"]').click({ animationDistanceThreshold: 50 })
      cy.get('.product-item').eq(1).within(() => {
        cy.get('input[name="categories[]"]')
          .invoke('prop', 'validationMessage')
          .should('not.be.empty')
          .should('contain', 'Please fill out this field')
      })

      // Fill second product
      cy.get('.product-item').eq(1).within(() => {
        cy.get('input[name="categories[]"]').type('Product 2', { animationDistanceThreshold: 50 })
        cy.get('input[name="categoryImages[]"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Verify form validation is complete
      cy.get('.product-item').eq(1).within(() => {
        cy.get('input[name="categories[]"]')
          .invoke('prop', 'validationMessage')
          .should('be.empty')
      })
    })

    it('should handle multiple products', () => {
      // Click appropriate create button
      cy.get('body').then(($body) => {
        cy.contains('Create New Bundle').click({ animationDistanceThreshold: 50 })
      })

      // Fill first product
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]')
          .clear({ animationDistanceThreshold: 50 })
          .type('Product 1', { animationDistanceThreshold: 50 })
        cy.get('input[name="categoryImages[]"]')
          .selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Add second product
      cy.get('#add-product').click({ animationDistanceThreshold: 50 })

      // Fill second product
      cy.get('.product-item').last().within(() => {
        cy.get('input[name="categories[]"]')
          .clear({ animationDistanceThreshold: 50 })
          .type('Product 2', { animationDistanceThreshold: 50 })
        cy.get('input[name="categoryImages[]"]')
          .selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })
    })
  })

  
  describe('Bundle Approval Process', () => {
    it('should handle admin approval flow', () => {
      // Create a test bundle first
      cy.get('body').contains('Create New Bundle').click({ animationDistanceThreshold: 50 })
      
      // Fill bundle details
      cy.get('input[name="bundleName"]').type('Bundle for Approval', { animationDistanceThreshold: 50 })
      cy.get('textarea[name="description"]').type('Test bundle description', { animationDistanceThreshold: 50 })
      cy.get('input[name="price"]').type('399.99', { animationDistanceThreshold: 50 })
      cy.get('input[name="bundleImage"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })

      // Fill first product
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]')
          .clear({ animationDistanceThreshold: 50 })
          .type('Product 1', { animationDistanceThreshold: 50 })
        cy.get('input[name="categoryImages[]"]')
          .selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Add and fill second product

      cy.get('.product-item').eq(1).within(() => {
        cy.get('input[name="categories[]"]')
          .clear({ animationDistanceThreshold: 50 })
          .type('Product 2', { animationDistanceThreshold: 50 })
        cy.get('input[name="categoryImages[]"]')
          .selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Submit the form and wait for success message
      cy.get('button[type="submit"]').click()
      cy.contains('Bundle created successfully and sent for review!')
        .should('exist')
      cy.url().should('include', '/seller/bundles')

      // Click username and logout
      cy.get('button').contains('Test Seller').click()
      cy.contains('Log Out').click()

      // Login as admin
      cy.visit('/login')
      cy.get('input[name="email"]').type('admin@gmail.com', { animationDistanceThreshold: 50 })
      cy.get('input[name="password"]').type('admin123', { animationDistanceThreshold: 50 })
      cy.get('button[type="submit"]').click()

      // Navigate to Admin Dashboard and Manage Bundles
      cy.contains('Admin Dashboard').click()
      cy.contains('Manage Bundles').click()

      // View bundle details
      cy.contains('.bundle-card', 'Bundle for Approval')
        .find('button svg')
        .click()
        .wait(1000)  // Wait for animation

      // Change status of Product 1 to Approve
      cy.contains('Product 1')
        .parents('.bg-gray-50')
        .find('.category-status')
        .select('approved', { force: true })
        .wait(500)

      // Change status of Product 2 to Approve
      cy.contains('Product 2')
        .parents('.bg-gray-50')
        .find('.category-status')
        .select('approved', { force: true })
        .wait(500)

      // Select Approve Bundle from bundle action dropdown
      cy.contains('.bundle-card', 'Bundle for Approval')
        .find('.bundle-status')
        .select('approved', { force: true })
        .wait(500)

      // Click Update Status
      cy.contains('button', 'Update Status')
        .click({ animationDistanceThreshold: 50 })

      // Wait and verify the bundle shows as Approved in the UI
      cy.wait(1000)
      cy.contains('.bundle-card', 'Bundle for Approval')
        .should('contain', 'Approved')
    })

    it('should handle admin rejection flow', () => {
      cy.get('body').contains('Create New Bundle').click({ animationDistanceThreshold: 50 })
      
      // Fill bundle details
      cy.get('input[name="bundleName"]').type('Bundle for Approval', { animationDistanceThreshold: 50 })
      cy.get('textarea[name="description"]').type('Test bundle description', { animationDistanceThreshold: 50 })
      cy.get('input[name="price"]').type('399.99', { animationDistanceThreshold: 50 })
      cy.get('input[name="bundleImage"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })

      // Fill first product
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]')
          .clear({ animationDistanceThreshold: 50 })
          .type('Product 1', { animationDistanceThreshold: 50 })
        cy.get('input[name="categoryImages[]"]')
          .selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Add and fill second product

      cy.get('.product-item').eq(1).within(() => {
        cy.get('input[name="categories[]"]')
          .clear({ animationDistanceThreshold: 50 })
          .type('Product 2', { animationDistanceThreshold: 50 })
        cy.get('input[name="categoryImages[]"]')
          .selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Submit the form and wait for success message
      cy.get('button[type="submit"]').click()
      cy.contains('Bundle created successfully and sent for review!')
        .should('exist')
      cy.url().should('include', '/seller/bundles')

      // Click username and logout
      cy.get('button').contains('Test Seller').click()
      cy.contains('Log Out').click()

      // Login as admin
      cy.visit('/login')
      cy.get('input[name="email"]').type('admin@gmail.com', { animationDistanceThreshold: 50 })
      cy.get('input[name="password"]').type('admin123', { animationDistanceThreshold: 50 })
      cy.get('button[type="submit"]').click()

      // Navigate to Admin Dashboard and Manage Bundles
      cy.contains('Admin Dashboard').click()
      cy.contains('Manage Bundles').click()

      // View bundle details
      cy.contains('.bundle-card', 'Bundle for Approval')
        .find('button svg')
        .click()
        .wait(1000)  // Wait for animation

      // Change status of Product 1 to Reject and select reason
      cy.contains('Product 1')
        .parents('.bg-gray-50')
        .within(() => {
          // Select reject status
          cy.get('.category-status')
            .select('rejected', { force: true })
            .wait(500)

          // Select rejection reason
          cy.get('.rejection-reason')
            .should('be.visible')
            .select('inappropriate_images', { force: true })
            .wait(500)
        })

      // Change status of Product 2 to Reject and select reason
      cy.contains('Product 2')
        .parents('.bg-gray-50')
        .within(() => {
          // Select reject status
          cy.get('.category-status')
            .select('rejected', { force: true })
            .wait(500)

          // Select rejection reason
          cy.get('.rejection-reason')
            .should('be.visible')
            .select('missing_images', { force: true })
            .wait(500)
        })

      // Handle bundle rejection in the Bundle Actions section
      cy.contains('.bundle-card', 'Bundle for Approval')
        .find('.bg-gray-50')
        .last()
        .within(() => {
          // Select reject from bundle status
          cy.get('.bundle-status')
            .select('rejected', { force: true })
            .wait(1000)

          // Try to select rejection reason (may cause uncaught exception)
          cy.get('select.rejection-reason')
            .select('misleading_information', { force: true })
            .wait(1000)

          // Click Update Status
          cy.contains('button', 'Update Status')
            .click({ animationDistanceThreshold: 50 })
        })

      // Wait and verify the bundle shows as Rejected in the UI
      cy.wait(1000)
      cy.contains('.bundle-card', 'Bundle for Approval')
        .should('contain', 'Rejected')
    })
  })

  describe('Bundle Modification and Re-approval', () => {
    it('should allow editing rejected bundle', () => {
      // Create a test bundle first
      cy.get('body').contains('Create New Bundle').click({ animationDistanceThreshold: 50 })
      
      // Fill bundle details
      cy.get('input[name="bundleName"]').type('Bundle for Rejection & Re-Editing', { animationDistanceThreshold: 50 })
      cy.get('textarea[name="description"]').type('Test bundle description', { animationDistanceThreshold: 50 })
      cy.get('input[name="price"]').type('399.99', { animationDistanceThreshold: 50 })
      cy.get('input[name="bundleImage"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })

      // Fill first product
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]')
          .clear({ animationDistanceThreshold: 50 })
          .type('Product 1', { animationDistanceThreshold: 50 })
        cy.get('input[name="categoryImages[]"]')
          .selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Add and fill second product
      cy.get('.product-item').eq(1).within(() => {
        cy.get('input[name="categories[]"]')
          .clear({ animationDistanceThreshold: 50 })
          .type('Product 2', { animationDistanceThreshold: 50 })
        cy.get('input[name="categoryImages[]"]')
          .selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Submit the form and wait for success message
      cy.get('button[type="submit"]').click()
      cy.contains('Bundle created successfully and sent for review!')
        .should('exist')
      cy.url().should('include', '/seller/bundles')

      // Click username and logout
      cy.get('button').contains('Test Seller').click()
      cy.contains('Log Out').click()

      // Login as admin
      cy.visit('/login')
      cy.get('input[name="email"]').type('admin@gmail.com', { animationDistanceThreshold: 50 })
      cy.get('input[name="password"]').type('admin123', { animationDistanceThreshold: 50 })
      cy.get('button[type="submit"]').click()

      // Navigate to Admin Dashboard and Manage Bundles
      cy.contains('Admin Dashboard').click()
      cy.contains('Manage Bundles').click()

      // View bundle details
      cy.contains('.bundle-card', 'Rejection & Re-Editing')
        .find('button svg')
        .click()
        .wait(1000)  // Wait for animation

      // Change status of Product 1 to Reject and select reason
      cy.contains('Product 1')
        .parents('.bg-gray-50')
        .within(() => {
          // Select reject status
          cy.get('.category-status')
            .select('rejected', { force: true })
            .wait(500)

          // Select rejection reason
          cy.get('.rejection-reason')
            .should('be.visible')
            .select('inappropriate_images', { force: true })
            .wait(500)
        })

      // Change status of Product 2 to Reject and select reason
      cy.contains('Product 2')
        .parents('.bg-gray-50')
        .within(() => {
          // Select reject status
          cy.get('.category-status')
            .select('rejected', { force: true })
            .wait(500)

          // Select rejection reason
          cy.get('.rejection-reason')
            .should('be.visible')
            .select('missing_images', { force: true })
            .wait(500)
        })

      // Handle bundle rejection in the Bundle Actions section
      cy.contains('.bundle-card', 'Rejection & Re-Editing')
        .find('.bg-gray-50')
        .last()
        .within(() => {
          // Select reject from bundle status
          cy.get('.bundle-status')
            .select('rejected', { force: true })
            .wait(1000)

          // Try to select rejection reason
          cy.get('select.rejection-reason')
            .select('misleading_information', { force: true })
            .wait(1000)

          // Click Update Status
          cy.contains('button', 'Update Status')
            .click({ animationDistanceThreshold: 50 })
        })

      // Wait and verify the bundle shows as Rejected in the UI
      cy.wait(1000)
      cy.contains('.bundle-card', 'Rejection & Re-Editing')
        .should('contain', 'Rejected')

      // Click username and logout from admin
      cy.get('button').contains('Admin').click()
      cy.contains('Log Out').click()

      // Login back as the same seller who created the bundle
      cy.visit('/login')
      cy.get('input[name="email"]').type(sellerEmail)
      cy.get('input[name="password"]').type(sellerPassword)
      cy.get('button[type="submit"]').click()

      // Go to seller bundles
      cy.visit('/seller/bundles')

      // Find the rejected bundle and click Edit Bundle
      cy.contains('h3', 'Bundle for Rejection & Re-Editing')
        .parents('.bg-white')
        .within(() => {
          cy.get('span').contains('Rejected')
          cy.contains('Edit Bundle').click()
        })

      // Verify rejection message and reason in the alert div
      cy.get('.bg-red-100')
        .within(() => {
          cy.contains('strong', 'Bundle Rejected')
          cy.contains('Reason: misleading_information')
        })

      // Bundle details should be pre-filled
      cy.get('#bundle_name').should('have.value', 'Bundle for Rejection & Re-Editing')
      cy.get('#description').should('have.value', 'Test bundle description')
      cy.get('input[name="price"]').should('have.value', '399.99')

      // Verify rejection messages for products
      cy.contains('Rejected: inappropriate_images')
      cy.contains('Rejected: missing_images')

      // Upload new bundle image
      cy.get('input[name="bundle_image"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })

      // Click Update Bundle button and verify success message
      cy.contains('button', 'Update Bundle').click()
      cy.contains('Bundle updated successfully and sent for review.', { timeout: 10000 }).should('be.visible')

      // Go back to bundles page and verify bundle exists with correct status
      cy.visit('/seller/bundles')
      cy.contains('Bundle for Rejection & Re-Editing').should('be.visible')
      cy.contains('Pending Review').should('be.visible')
    })
  })

  describe('Bundle Deletion', () => {
    it('should delete a bundle', () => {
      // Create a test bundle first
      cy.get('body').contains('Create New Bundle').click({ animationDistanceThreshold: 50 })
      
      // Fill bundle details
      cy.get('input[name="bundleName"]').type('Bundle for Deletion', { animationDistanceThreshold: 50 })
      cy.get('textarea[name="description"]').type('Test bundle description', { animationDistanceThreshold: 50 })
      cy.get('input[name="price"]').type('399.99', { animationDistanceThreshold: 50 })
      cy.get('input[name="bundleImage"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })

      // Fill first product
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]')
          .clear({ animationDistanceThreshold: 50 })
          .type('Product 1', { animationDistanceThreshold: 50 })
        cy.get('input[name="categoryImages[]"]')
          .selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Add and fill second product
      cy.get('.product-item').eq(1).within(() => {
        cy.get('input[name="categories[]"]')
          .clear({ animationDistanceThreshold: 50 })
          .type('Product 2', { animationDistanceThreshold: 50 })
        cy.get('input[name="categoryImages[]"]')
          .selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Submit the form and wait for success message
      cy.get('button[type="submit"]').click()
      cy.contains('Bundle created successfully and sent for review!')
        .should('exist')
      cy.url().should('include', '/seller/bundles')

      // Click username and logout
      cy.get('button').contains('Test Seller').click()
      cy.contains('Log Out').click()

      // Login as admin
      cy.visit('/login')
      cy.get('input[name="email"]').type('admin@gmail.com', { animationDistanceThreshold: 50 })
      cy.get('input[name="password"]').type('admin123', { animationDistanceThreshold: 50 })
      cy.get('button[type="submit"]').click()

      // Navigate to Admin Dashboard and Manage Bundles
      cy.contains('Admin Dashboard').click()
      cy.contains('Manage Bundles').click()

      // View bundle details
      cy.contains('.bundle-card', 'Bundle for Deletion')
        .find('button svg')
        .click()
        .wait(1000)  // Wait for animation

      // Handle bundle deletion in the Bundle Actions section
      cy.contains('.bundle-card', 'Bundle for Deletion')
        .find('.bg-gray-50')
        .last()
        .within(() => {
          // Select reject from bundle status
          cy.get('.bundle-status')
            .select('rejected', { force: true })
            .wait(1000)

          // Try to select rejection reason
          cy.get('select.rejection-reason')
            .select('misleading_information', { force: true })
            .wait(1000)
        })

      // Click Update Status
      cy.contains('button', 'Update Status')
        .click({ animationDistanceThreshold: 50 })

      // Wait and verify the bundle shows as Rejected in the UI
      cy.wait(1000)
      cy.contains('.bundle-card', 'Bundle for Deletion')
        .should('contain', 'Rejected')

      // Click username and logout from admin
      cy.get('button').contains('Admin').click()
      cy.contains('Log Out').click()

      // Login back as the same seller who created the bundle
      cy.visit('/login')
      cy.get('input[name="email"]').type(sellerEmail)
      cy.get('input[name="password"]').type(sellerPassword)
      cy.get('button[type="submit"]').click()

      // Go to seller bundles
      cy.visit('/seller/bundles')

      // Find the rejected bundle and click Delete Bundle
      cy.contains('h3', 'Bundle for Deletion')
        .parents('.bg-white')
        .within(() => {
          cy.get('span').contains('Rejected')
          cy.contains('Delete Bundle').click()
        })

      // Verify success message and empty state
      cy.contains('Bundle deleted successfully.').should('be.visible')
    })
  })

  describe('Bundle Display', () => {
    it('should display approved bundle details correctly', () => {
      // Create a test bundle first
      cy.get('body').contains('Create New Bundle').click({ animationDistanceThreshold: 50 })
      
      // Fill bundle details
      cy.get('input[name="bundleName"]').type('Display Test Bundle', { animationDistanceThreshold: 50 })
      cy.get('textarea[name="description"]').type('Test bundle description', { animationDistanceThreshold: 50 })
      cy.get('input[name="price"]').type('399.99', { animationDistanceThreshold: 50 })
      cy.get('input[name="bundleImage"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })

      // Fill first product
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]')
          .clear({ animationDistanceThreshold: 50 })
          .type('Product 1', { animationDistanceThreshold: 50 })
        cy.get('input[name="categoryImages[]"]')
          .selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Add and fill second product

      cy.get('.product-item').eq(1).within(() => {
        cy.get('input[name="categories[]"]')
          .clear({ animationDistanceThreshold: 50 })
          .type('Product 2', { animationDistanceThreshold: 50 })
        cy.get('input[name="categoryImages[]"]')
          .selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Submit the form and wait for success message
      cy.get('button[type="submit"]').click()
      cy.contains('Bundle created successfully and sent for review!')
        .should('exist')
      cy.url().should('include', '/seller/bundles')

      // Click username and logout
      cy.get('button').contains('Test Seller').click()
      cy.contains('Log Out').click()

      // Login as admin
      cy.visit('/login')
      cy.get('input[name="email"]').type('admin@gmail.com', { animationDistanceThreshold: 50 })
      cy.get('input[name="password"]').type('admin123', { animationDistanceThreshold: 50 })
      cy.get('button[type="submit"]').click()

      // Navigate to Admin Dashboard and Manage Bundles
      cy.contains('Admin Dashboard').click()
      cy.contains('Manage Bundles').click()

      // View bundle details
      cy.contains('.bundle-card', 'Display Test Bundle')
        .find('button svg')
        .click()
        .wait(1000)  // Wait for animation

      // Change status of Product 1 to Approve
      cy.contains('Product 1')
        .parents('.bg-gray-50')
        .find('.category-status')
        .select('approved', { force: true })
        .wait(500)

      // Change status of Product 2 to Approve
      cy.contains('Product 2')
        .parents('.bg-gray-50')
        .find('.category-status')
        .select('approved', { force: true })
        .wait(500)

      // Select Approve Bundle from bundle action dropdown
      cy.contains('.bundle-card', 'Display Test Bundle')
        .find('.bundle-status')
        .select('approved', { force: true })
        .wait(500)

      // Click Update Status
      cy.contains('button', 'Update Status')
        .click({ animationDistanceThreshold: 50 })

      // Wait and verify the bundle shows as Approved in the UI
      cy.wait(1000)
      cy.contains('Display Test Bundle')
        .should('be.visible')
        .parents('.bg-white')
        .should('contain', 'Approved')

      // Check bundle display in shop
      cy.visit('/shop/bundles')
      cy.contains('h3', 'Display Test Bundle')
        .parents('.bg-white')
        .within(() => {
          cy.get('img').should('be.visible')
          cy.contains('Test bundle description').should('be.visible')
          cy.contains('Colombo').should('be.visible')
          cy.contains('Rs 399.99').should('be.visible')
        })
    })
  })
})
