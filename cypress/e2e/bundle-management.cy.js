describe('Bundle Management', () => {
  const sellerEmail = `seller${new Date().getTime()}@example.com`

  before(() => {
    // Register as a seller
    cy.visit('/register')
    cy.get('form').within(() => {
      cy.get('#name').type('Test Seller')
      cy.get('#email').type(sellerEmail)
      cy.get('#location').select('Colombo')
      cy.get('#password').type('Password123!')
      cy.get('#password_confirmation').type('Password123!')
    })
    cy.get('form').submit()

    // Switch to seller mode
    cy.url().should('include', '/dashboard1')
    cy.contains('button', "I'm a Seller").click()
    cy.url().should('include', '/seller/dashboard')
  })

  beforeEach(() => {
    // Login if needed using the same email
    cy.visit('/seller/bundles')
    cy.url().then((url) => {
      if (url.includes('/login')) {
        cy.visit('/login')
        cy.get('#email').type(sellerEmail)
        cy.get('#password').type('Password123!')
        cy.get('form').submit()
        cy.visit('/seller/bundles')
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
          .should('equal', 'Please fill out this field.')
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

      // Submit empty form
      cy.get('button[type="submit"]').click()

      // Check bundle name validation
      cy.get('input[name="bundleName"]')
        .invoke('prop', 'validationMessage')
        .should('not.be.empty')

      // Fill bundle name and try again
      cy.get('input[name="bundleName"]').type('Test Bundle')
      cy.get('button[type="submit"]').click()

      // Check description validation
      cy.get('textarea[name="description"]')
        .invoke('prop', 'validationMessage')
        .should('not.be.empty')

      // Fill description and try again
      cy.get('textarea[name="description"]').type('Test Description')
      cy.get('button[type="submit"]').click()

      // Check price validation
      cy.get('input[name="price"]')
        .invoke('prop', 'validationMessage')
        .should('not.be.empty')

      // Fill price and try again
      cy.get('input[name="price"]').type('199.99')
      cy.get('button[type="submit"]').click()

      // Check bundle image validation
      cy.get('input[name="bundleImage"]')
        .invoke('prop', 'validationMessage')
        .should('not.be.empty')

      // Add bundle image
      cy.get('input[name="bundleImage"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })

      // Check first product validation
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]')
          .invoke('prop', 'validationMessage')
          .should('not.be.empty')
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
      cy.get('button[type="submit"]').click()
      cy.get('.product-item').eq(1).within(() => {
        cy.get('input[name="categories[]"]')
          .invoke('prop', 'validationMessage')
          .should('not.be.empty')
      })

      // Fill second product
      cy.get('.product-item').eq(1).within(() => {
        cy.get('input[name="categories[]"]').type('Product 2')
        cy.get('input[name="categoryImages[]"]').selectFile('cypress/fixtures/product-image.jpg', { force: true })
      })

      // Set up interception and submit form
      cy.intercept({
        method: 'POST',
        url: '**/seller/bundles'
      }).as('bundleSubmit')

      // Submit form and verify redirect
      cy.get('form').submit()
      cy.url().should('include', '/seller/bundles')
    })

    it('should handle product selection', () => {
      // Click appropriate create button
      cy.get('body').then(($body) => {
        if ($body.text().includes("You haven't created any bundles yet.")) {
          cy.contains('Create Your First Bundle').click()
        } else {
          cy.contains('Create New Bundle').click()
        }
      })

      // Add multiple products
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 1')
        cy.get('input[name="categoryImages[]"]').selectFile({
          contents: 'cypress/fixtures/product-image.jpg',
          mimeType: 'image/jpeg'
        }, { force: true })
      })
      cy.get('#add-product').click()
      cy.get('.product-item').last().within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 2')
        cy.get('input[name="categoryImages[]"]').selectFile({
          contents: 'cypress/fixtures/product-image.jpg',
          mimeType: 'image/jpeg'
        }, { force: true })
      })

      // Remove a product
      cy.get('.remove-product-btn').first().click()
      cy.get('.product-item').should('have.length', 1)

      // Add product again
      cy.get('#add-product').click()
      cy.get('.product-item').last().within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 3')
        cy.get('input[name="categoryImages[]"]').selectFile({
          contents: 'cypress/fixtures/product-image.jpg',
          mimeType: 'image/jpeg'
        }, { force: true })
      })
    })
  })

  describe('Bundle Modification and Re-approval', () => {
    it('should allow editing rejected bundle', () => {
      // Create initial bundle
      cy.get('body').then(($body) => {
        if ($body.text().includes("You haven't created any bundles yet.")) {
          cy.contains('Create Your First Bundle').click()
        } else {
          cy.contains('Create New Bundle').click()
        }
      })
      
      cy.get('input[name="bundleName"]').type('Bundle for Modification')
      cy.get('textarea[name="description"]').type('Test bundle description')
      cy.get('input[name="price"]').type('399.99')
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 1')
        cy.get('input[name="categoryImages[]"]').selectFile({
          contents: 'cypress/fixtures/product-image.jpg',
          mimeType: 'image/jpeg'
        }, { force: true })
      })
      cy.get('input[name="bundleImage"]').selectFile({
        contents: 'cypress/fixtures/product-image.jpg',
        mimeType: 'image/jpeg'
      }, { force: true })
      cy.get('form[action*="/seller/bundles"]').submit()

      // Login as admin and reject the bundle
      cy.visit('/login')
      cy.get('#email').type('admin@edume.com')
      cy.get('#password').type('admin123')
      cy.get('form').submit()

      cy.visit('/admin/bundles')
      cy.contains('.bundle-card', 'Bundle for Modification').within(() => {
        cy.get('.reject-btn').click()
      })
      cy.get('#rejection_reason').type('Price needs adjustment')
      cy.get('#submit-rejection').click()

      // Back to seller and edit bundle
      cy.visit('/seller/bundles')
      cy.contains('.bundle-card', 'Bundle for Modification').within(() => {
        cy.contains('Edit Bundle').click()
      })

      // Update bundle details
      const newName = `Updated Bundle ${new Date().getTime()}`
      cy.get('input[name="bundleName"]').clear().type(newName)
      cy.get('input[name="price"]').clear().type('299.99')

      // Submit updates
      cy.get('form[action*="/seller/bundles"]').submit()

      // Verify bundle status returned to pending
      cy.contains(newName).should('be.visible')
      cy.contains('Pending Approval').should('be.visible')
    })
  })

  describe('Bundle Approval Process', () => {
    it('should handle admin approval flow', () => {
      // Create a test bundle first
      cy.get('body').then(($body) => {
        if ($body.text().includes("You haven't created any bundles yet.")) {
          cy.contains('Create Your First Bundle').click()
        } else {
          cy.contains('Create New Bundle').click()
        }
      })
      
      cy.get('input[name="bundleName"]').type('Bundle for Approval')
      cy.get('textarea[name="description"]').type('Test bundle description')
      cy.get('input[name="price"]').type('399.99')
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 1')
        cy.get('input[name="categoryImages[]"]').selectFile({
          contents: 'cypress/fixtures/product-image.jpg',
          mimeType: 'image/jpeg'
        }, { force: true })
      })
      cy.get('input[name="bundleImage"]').selectFile({
        contents: 'cypress/fixtures/product-image.jpg',
        mimeType: 'image/jpeg'
      }, { force: true })
      cy.get('form[action*="/seller/bundles"]').submit()

      // Login as admin
      cy.visit('/login')
      cy.get('#email').type('admin@edume.com')
      cy.get('#password').type('admin123')
      cy.get('form').submit()

      // Approve the bundle
      cy.visit('/admin/bundles')
      cy.contains('.bundle-card', 'Bundle for Approval').within(() => {
        cy.get('.approve-btn').click()
      })

      // Verify approval
      cy.contains('Bundle approved successfully').should('be.visible')

      // Verify in shop
      cy.visit('/shop/bundles')
      cy.contains('Bundle for Approval').should('be.visible')
    })
  })

  describe('Bundle Display', () => {
    it('should display approved bundle details correctly', () => {
      // Create initial bundle
      cy.get('body').then(($body) => {
        if ($body.text().includes("You haven't created any bundles yet.")) {
          cy.contains('Create Your First Bundle').click()
        } else {
          cy.contains('Create New Bundle').click()
        }
      })
      
      cy.get('input[name="bundleName"]').type('Display Test Bundle')
      cy.get('textarea[name="description"]').type('Test bundle description')
      cy.get('input[name="price"]').type('399.99')
      cy.get('.product-item').first().within(() => {
        cy.get('input[name="categories[]"]').clear().type('Product 1')
        cy.get('input[name="categoryImages[]"]').selectFile({
          contents: 'cypress/fixtures/product-image.jpg',
          mimeType: 'image/jpeg'
        }, { force: true })
      })
      cy.get('input[name="bundleImage"]').selectFile({
        contents: 'cypress/fixtures/product-image.jpg',
        mimeType: 'image/jpeg'
      }, { force: true })
      cy.get('form[action*="/seller/bundles"]').submit()

      // Login as admin and approve
      cy.visit('/login')
      cy.get('#email').type('admin@edume.com')
      cy.get('#password').type('admin123')
      cy.get('form').submit()

      cy.visit('/admin/bundles')
      cy.contains('.bundle-card', 'Display Test Bundle').within(() => {
        cy.get('.approve-btn').click()
      })

      // Check bundle display in shop
      cy.visit('/shop/bundles')
      cy.contains('.bundle-card', 'Display Test Bundle').within(() => {
        cy.get('img.bundle-image').should('be.visible')
        cy.contains('399.99').should('be.visible')
        cy.get('.bundle-products').should('be.visible')
        cy.get('.bundle-savings').should('be.visible')
      })
    })
  })
})
