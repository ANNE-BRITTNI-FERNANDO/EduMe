describe('Cart Management', () => {
  // Common setup before all tests
  before(() => {
    // Register and login as a seller first to create test products
    cy.visit('/register')
    cy.get('#name').type('Test Seller')
    cy.get('#email').type(`seller${new Date().getTime()}@example.com`)
    cy.get('#location').select('Colombo')
    cy.get('#password').type('Password123!')
    cy.get('#password_confirmation').type('Password123!')
    cy.get('form').submit()

    // Switch to seller mode
    cy.contains("I'm a Seller").click()

    // Create test products for cart testing
    createTestProduct('Test Book 1', '99.99', 'Textbooks & Reference Books')
    createTestProduct('Test Book 2', '149.99', 'Textbooks & Reference Books')
    createTestProduct('Test Bundle', '199.99', 'Study Bundles')

    // Register and login as a buyer
    cy.visit('/register')
    cy.get('#name').type('Test Buyer')
    cy.get('#email').type(`buyer${new Date().getTime()}@example.com`)
    cy.get('#location').select('Colombo')
    cy.get('#password').type('Password123!')
    cy.get('#password_confirmation').type('Password123!')
    cy.get('form').submit()
  })

  beforeEach(() => {
    // Clear cart before each test
    cy.clearCart() // You'll need to implement this command
    cy.visit('/products')
  })

  it('should add single product to cart', () => {
    // Find and add first product
    cy.contains('.bg-white', 'Test Book 1').within(() => {
      cy.get('button').contains('Add to Cart').click()
    })

    // Verify cart
    cy.get('[data-test="cart-count"]').should('contain', '1')
    cy.visit('/cart')
    cy.contains('Test Book 1').should('be.visible')
    cy.get('[data-test="cart-total"]').should('contain', '99.99')
  })

  it('should add multiple products to cart', () => {
    // Add first product
    cy.contains('.bg-white', 'Test Book 1').within(() => {
      cy.get('button').contains('Add to Cart').click()
    })

    // Add second product
    cy.contains('.bg-white', 'Test Book 2').within(() => {
      cy.get('button').contains('Add to Cart').click()
    })

    // Verify cart
    cy.get('[data-test="cart-count"]').should('contain', '2')
    cy.visit('/cart')
    cy.contains('Test Book 1').should('be.visible')
    cy.contains('Test Book 2').should('be.visible')
    cy.get('[data-test="cart-total"]').should('contain', '249.98')
  })

  it('should add bundle to cart', () => {
    // Add bundle
    cy.contains('.bg-white', 'Test Bundle').within(() => {
      cy.get('button').contains('Add to Cart').click()
    })

    // Verify cart
    cy.get('[data-test="cart-count"]').should('contain', '1')
    cy.visit('/cart')
    cy.contains('Test Bundle').should('be.visible')
    cy.get('[data-test="cart-total"]').should('contain', '199.99')
  })

  it('should remove items from cart', () => {
    // Add products first
    cy.contains('.bg-white', 'Test Book 1').within(() => {
      cy.get('button').contains('Add to Cart').click()
    })
    cy.contains('.bg-white', 'Test Book 2').within(() => {
      cy.get('button').contains('Add to Cart').click()
    })

    // Go to cart and remove first item
    cy.visit('/cart')
    cy.contains('.cart-item', 'Test Book 1').within(() => {
      cy.get('button').contains('Remove').click()
    })

    // Verify cart updated
    cy.get('[data-test="cart-count"]').should('contain', '1')
    cy.contains('Test Book 1').should('not.exist')
    cy.contains('Test Book 2').should('be.visible')
    cy.get('[data-test="cart-total"]').should('contain', '149.99')
  })

  it('should calculate cart total correctly without delivery', () => {
    // Add multiple products
    cy.contains('.bg-white', 'Test Book 1').within(() => {
      cy.get('button').contains('Add to Cart').click()
    })
    cy.contains('.bg-white', 'Test Book 2').within(() => {
      cy.get('button').contains('Add to Cart').click()
    })
    cy.contains('.bg-white', 'Test Bundle').within(() => {
      cy.get('button').contains('Add to Cart').click()
    })

    // Verify subtotal and total
    cy.visit('/cart')
    cy.get('[data-test="cart-subtotal"]').should('contain', '449.97')
    cy.get('[data-test="cart-total"]').should('contain', '449.97')
  })

  it('should calculate cart total correctly with delivery', () => {
    // Add products
    cy.contains('.bg-white', 'Test Book 1').within(() => {
      cy.get('button').contains('Add to Cart').click()
    })

    // Go to cart and add delivery details
    cy.visit('/cart')
    cy.get('[data-test="delivery-address"]').type('123 Test St, Colombo')
    cy.get('[data-test="delivery-method"]').select('Standard Delivery')

    // Verify total includes delivery fee
    cy.get('[data-test="delivery-fee"]').should('be.visible')
    cy.get('[data-test="cart-total"]').should('not.contain', '99.99')
  })

  it('should handle products from multiple sellers', () => {
    // Add product from first seller
    cy.contains('.bg-white', 'Test Book 1').within(() => {
      cy.get('button').contains('Add to Cart').click()
    })

    // Login as second seller and create product
    cy.visit('/register')
    cy.get('#name').type('Second Seller')
    cy.get('#email').type(`seller2${new Date().getTime()}@example.com`)
    cy.get('#location').select('Colombo')
    cy.get('#password').type('Password123!')
    cy.get('#password_confirmation').type('Password123!')
    cy.get('form').submit()
    cy.contains("I'm a Seller").click()

    createTestProduct('Seller 2 Book', '79.99', 'Textbooks & Reference Books')

    // Login back as buyer
    cy.login('buyer') // You'll need to implement this command

    // Add product from second seller
    cy.visit('/products')
    cy.contains('.bg-white', 'Seller 2 Book').within(() => {
      cy.get('button').contains('Add to Cart').click()
    })

    // Verify cart shows products from both sellers
    cy.visit('/cart')
    cy.contains('Test Book 1').should('be.visible')
    cy.contains('Seller 2 Book').should('be.visible')
    cy.get('[data-test="cart-total"]').should('contain', '179.98')
  })

  it('should persist cart after page reload', () => {
    // Add products
    cy.contains('.bg-white', 'Test Book 1').within(() => {
      cy.get('button').contains('Add to Cart').click()
    })

    // Reload page
    cy.reload()

    // Verify cart still contains items
    cy.get('[data-test="cart-count"]').should('contain', '1')
    cy.visit('/cart')
    cy.contains('Test Book 1').should('be.visible')
  })

  it('should merge cart after login', () => {
    // Add product while logged out
    cy.clearCookies()
    cy.visit('/products')
    cy.contains('.bg-white', 'Test Book 1').within(() => {
      cy.get('button').contains('Add to Cart').click()
    })

    // Login and verify cart persists
    cy.login('buyer')
    cy.get('[data-test="cart-count"]').should('contain', '1')
    cy.visit('/cart')
    cy.contains('Test Book 1').should('be.visible')
  })
})

// Helper function to create test products
function createTestProduct(name, price, category) {
  cy.visit('/seller/products/create')
  cy.get('#product_name').type(name)
  cy.get('#description').type('Test description')
  cy.get('#price').type(price)
  cy.get('#category').select(category)
  cy.get('#image').selectFile('cypress/fixtures/product-image.jpg')
  cy.get('[data-test="product-form"]').submit()
}
