describe('Product Management', () => {
  beforeEach(() => {
    // Generate unique email for registration
    const email = `seller${new Date().getTime()}@example.com`

    // Visit register page with failOnStatusCode: false to handle potential server issues
    cy.visit('/register', { failOnStatusCode: false })

    // Wait for the page to be ready
    cy.get('form', { timeout: 10000 }).should('be.visible').within(() => {
      cy.get('#name').should('be.visible').type('Test Seller')
      cy.get('#email').should('be.visible').type(email)
      cy.get('#location').should('be.visible').select('Colombo')
      cy.get('#password').should('be.visible').type('Password123!')
      cy.get('#password_confirmation').should('be.visible').type('Password123!')
    })

    cy.get('form').submit()

    // Verify registration was successful
    cy.url().should('include', '/dashboard1')

    // Navigate to seller dashboard
    cy.contains('button', "I'm a Seller").click()
    cy.url().should('include', '/seller/dashboard')

    // Navigate to products page
    cy.visit('/seller/products')
    cy.url().should('include', '/seller/products')
  })

  describe('Product Creation', () => {
    beforeEach(() => {
      // Click on Add New Product using the link text and class
      cy.contains('a', 'Add New Product')
        .should('have.class', 'bg-indigo-600')
        .click()
      
      // Verify we're on the create product page
      cy.url().should('include', '/seller/products/create')
      
      // Wait for form to be visible
      cy.get('[data-test="product-form"]').should('be.visible')
    })

    it('should create product with all valid fields', () => {
      const timestamp = new Date().getTime()
      const productName = `Test Product ${timestamp}`
      
      // Fill in product details using correct field IDs
      cy.get('#product_name').should('be.visible').type(productName)
      cy.get('#description').should('be.visible')
        .type('This is a test product with all valid fields')
      cy.get('#price').should('be.visible').type('99.99')
      
      // Select the first available category
      cy.get('#category').should('be.visible').find('option')
        .then($options => {
          // Get the first option that has a value (skip the placeholder)
          const firstCategory = $options.filter((i, el) => el.value !== '').first()
          cy.get('#category').select(firstCategory.val())
        })
      
      // Upload main product image
      cy.get('#image').selectFile('cypress/fixtures/product-image.jpg')
      
      // Submit the product form
      cy.get('[data-test="product-form"]').submit()
      
      // Verify success
      cy.url().should('not.include', '/create')
      cy.contains(productName).should('be.visible')
    })

    it('should show error for missing required fields', () => {
      // Submit empty form
      cy.get('[data-test="product-form"]').submit()

      // Check for error messages
      cy.get('.mt-2').should('be.visible') // Laravel validation error messages
      
      // Check browser validation messages
      cy.get('#product_name').should('be.visible')
        .then($el => expect($el[0].validationMessage).to.not.be.empty)
      
      cy.get('#price').should('be.visible')
        .then($el => expect($el[0].validationMessage).to.not.be.empty)
      
      cy.get('#category').should('be.visible')
        .then($el => expect($el[0].validationMessage).to.not.be.empty)
      
      cy.get('#image').should('be.visible')
        .then($el => expect($el[0].validationMessage).to.not.be.empty)
    })

    it('should validate product price', () => {
      // Test negative price
      cy.get('#price').type('-10')
      cy.get('[data-test="product-form"]').submit()
      cy.get('#price').then($el => {
        expect($el[0].validationMessage).to.not.be.empty
      })

      // Test excessive price
      cy.get('#price').clear().type('999999999')
      cy.get('[data-test="product-form"]').submit()
      cy.get('.mt-2').should('be.visible') // Laravel validation error
    })

    it('should format product description correctly', () => {
      cy.get('#description')
        .type('Line 1\nLine 2\nLine 3')
        .should('have.value', 'Line 1\nLine 2\nLine 3')
    })

    it('should handle multiple image uploads', () => {
      const timestamp = new Date().getTime()
      const productName = `Test Product ${timestamp}`
      
      // Fill in required fields
      cy.get('#product_name').type(productName)
      cy.get('#description').type('Test description')
      cy.get('#price').type('99.99')
      cy.get('#category').find('option')
        .then($options => {
          const firstCategory = $options.filter((i, el) => el.value !== '').first()
          cy.get('#category').select(firstCategory.val())
        })
      
      // Upload main and additional images
      cy.get('#image').selectFile('cypress/fixtures/product-image.jpg')
      cy.get('#additional_images').selectFile([
        'cypress/fixtures/product-additional-image.jpg'
      ])
      
      // Submit the product form
      cy.get('[data-test="product-form"]').submit()
      
      // Verify success
      cy.url().should('not.include', '/create')
      
      // Find the newly created product card
      cy.contains('.bg-white', productName).within(() => {
        // Check that the main image exists
        cy.get('img').should('be.visible')
          .and('have.attr', 'src')
          .and('include', '/storage/')
      })
    })

    it('should validate invalid image formats', () => {
      // Fill in required fields
      const timestamp = new Date().getTime()
      cy.get('#product_name').type(`Test Product ${timestamp}`)
      cy.get('#description').type('Test description')
      cy.get('#price').type('99.99')
      cy.get('#category').find('option')
        .then($options => {
          const firstCategory = $options.filter((i, el) => el.value !== '').first()
          cy.get('#category').select(firstCategory.val())
        })
      
      // Try to upload an invalid file
      cy.get('#image').selectFile('cypress/fixtures/invalid.txt', { force: true })
      
      // Submit the form
      cy.get('[data-test="product-form"]').submit()
      
      // Check for error message
      cy.get('.mt-2').should('be.visible')
        .and('contain', 'The image field must be an image')
    })
  })

  describe('Additional Product Editing Tests', () => {
    beforeEach(() => {
      // Create a test product first
      cy.contains('a', 'Add New Product')
        .should('have.class', 'bg-indigo-600')
        .click()
      
      const timestamp = new Date().getTime()
      cy.get('#product_name').type(`Test Product ${timestamp}`)
      cy.get('#description').type('Test description')
      cy.get('#price').type('99.99')
      cy.get('#category').find('option')
        .then($options => {
          const firstCategory = $options.filter((i, el) => el.value !== '').first()
          cy.get('#category').select(firstCategory.val())
        })
      
      // Upload main and additional images
      cy.get('#image').selectFile('cypress/fixtures/product-image.jpg')
      cy.get('#additional_images').selectFile([
        'cypress/fixtures/product-additional-image.jpg',
        'cypress/fixtures/product-additional-image.jpg'
      ])
      
      cy.get('[data-test="product-form"]').submit()
      
      // Navigate to edit page
      cy.contains(`Test Product ${timestamp}`).should('be.visible')
        .parents('.bg-white')
        .find('a')
        .contains('Edit')
        .click()
    })

    it('should modify product prices', () => {
      // Test different price updates
      cy.get('#price').clear().type('149.99')
      cy.get('button[type="submit"]').click()
      cy.url().should('not.include', '/edit')
      cy.contains('149.99').should('be.visible')
    })

    it('should update product main image', () => {
      // Update main image
      cy.get('#image').selectFile('cypress/fixtures/product-image.jpg', { force: true })
      cy.get('button[type="submit"]').click()
      cy.url().should('not.include', '/edit')
      cy.get('img[alt*="Test Product"]').should('be.visible')
    })

    it('should remove additional images', () => {
      // Find and click remove button for additional images
      cy.get('button')
        .contains('Remove')
        .first()
        .click()

      // Wait for the removal to complete
      cy.wait(1000)
      
      cy.get('button[type="submit"]').click()
      cy.url().should('not.include', '/edit')
      
      // Verify image was removed by checking the number of additional images
      cy.get('img[alt="Additional image"]')
        .should('have.length.lessThan', 2)
    })
  })

  describe('Product Editing', () => {
    beforeEach(() => {
      // Navigate to create product page
      cy.contains('a', 'Add New Product')
        .should('have.class', 'bg-indigo-600')
        .click()
      
      // Create a test product first
      const timestamp = new Date().getTime()
      cy.get('#product_name').type(`Test Product ${timestamp}`)
      cy.get('#description').type('Test description')
      cy.get('#price').type('99.99')
      cy.get('#category').find('option')
        .then($options => {
          const firstCategory = $options.filter((i, el) => el.value !== '').first()
          cy.get('#category').select(firstCategory.val())
        })
      
      // Upload main image
      cy.get('#image').selectFile('cypress/fixtures/product-image.jpg')
      
      // Submit the form
      cy.get('[data-test="product-form"]').submit()
      
      // Wait for product to be created and click edit
      cy.contains(`Test Product ${timestamp}`).should('be.visible')
        .parents('.bg-white')
        .find('a')
        .contains('Edit')
        .click()
    })

    it('should update product information', () => {
      const newTimestamp = new Date().getTime()
      const updatedName = `Updated Product ${newTimestamp}`
      
      // Update product details
      cy.get('#product_name').clear().type(updatedName)
      cy.get('#description').clear().type('Updated description')
      cy.get('#price').clear().type('149.99')
      
      // Select a different category
      cy.get('#category').find('option')
        .then($options => {
          // Get the second available category (skip first as it might be current)
          const categories = $options.filter((i, el) => el.value !== '')
          if (categories.length > 1) {
            cy.get('#category').select(categories.eq(1).val())
          }
        })
      
      // Submit the form using the submit button
      cy.get('button[type="submit"]').click()
      
      // Verify the update
      cy.url().should('not.include', '/edit')
      cy.contains(updatedName).should('be.visible')
    })
  })

  describe('Product Deletion', () => {
    beforeEach(() => {
      // Navigate to create product page first
      cy.contains('a', 'Add New Product')
        .should('have.class', 'bg-indigo-600')
        .click()

      // Create a test product for deletion
      const timestamp = new Date().getTime()
      const productName = `Test Product ${timestamp}`
      
      cy.get('#product_name').type(productName)
      cy.get('#description').type('Test description')
      cy.get('#price').type('99.99')
      cy.get('#category').find('option')
        .then($options => {
          const firstCategory = $options.filter((i, el) => el.value !== '').first()
          cy.get('#category').select(firstCategory.val())
        })
      
      // Upload main image
      cy.get('#image').selectFile('cypress/fixtures/product-image.jpg')
      
      // Submit the form
      cy.get('[data-test="product-form"]').submit()

      // Verify product was created
      cy.contains(productName).should('be.visible')
    })

    it('should delete product', () => {
      // Store the product name to check later
      cy.contains('h3', 'Test Product').invoke('text').then((productName) => {
        // Find and click delete button in the product card
        cy.contains('h3', 'Test Product')
          .parents('.bg-white')
          .first()
          .within(() => {
            cy.get('button').contains('Delete').click()
          })

        // Wait for page reload after delete
        cy.wait(1000)

        // Verify product is deleted by checking it no longer exists
        cy.contains(productName).should('not.exist')
      })
    })
  })
})
