describe('Budget Management', () => {
    const testUser = {
        name: 'Test User',
        email: 'testuser@example.com',
        password: 'Password123!',
        location: 'Colombo'
    }

    before(() => {
        // Register new user
        cy.visit('/register')
        cy.get('#name').type(testUser.name)
        cy.get('#email').type(testUser.email)
        cy.get('#location').select(testUser.location)
        cy.get('#password').type(testUser.password)
        cy.get('#password_confirmation').type(testUser.password)
        cy.get('form').submit()

        // Verify registration success
        cy.url().should('not.include', '/register')
    })

    beforeEach(() => {
        // Visit the login page
        cy.visit('/login')

        // Login with registered credentials
        cy.get('#email').type(testUser.email)
        cy.get('#password').type(testUser.password)
        cy.get('form').submit()

        // Verify successful login and redirect
        cy.url().should('not.include', '/login')

        // Click on "I'm a buyer" button
        cy.contains('I\'m a buyer').click()
    })

    describe('Budget Setting', () => {
        it('should successfully set a monthly budget', () => {
            // Click on Budget in nav bar
            cy.get('nav').contains('Budget').click()

            // Verify we're on the budget page
            cy.url().should('include', '/budget')

            // Set amount
            cy.get('input[name="amount"]').type('1000')

            // Select monthly from dropdown
            cy.get('select[name="period"]').select('monthly')

            // Click set budget button
            cy.contains('button', 'Set Budget').click()

            // Verify success message
            cy.contains('Budget has been set successfully').should('be.visible')

            // Verify budget is displayed
            cy.contains('Current Budget: $1,000').should('be.visible')
            cy.contains('Period: Monthly').should('be.visible')
        })

        it('should successfully set a yearly budget', () => {
            // Click on Budget in nav bar
            cy.get('nav').contains('Budget').click()

            // Set amount
            cy.get('input[name="amount"]').type('12000')

            // Select yearly from dropdown
            cy.get('select[name="period"]').select('yearly')

            // Click set budget button
            cy.contains('button', 'Set Budget').click()

            // Verify success message
            cy.contains('Budget has been set successfully').should('be.visible')

            // Verify budget is displayed
            cy.contains('Current Budget: $12,000').should('be.visible')
            cy.contains('Period: Yearly').should('be.visible')
        })

        it('should validate budget amount', () => {
            // Click on Budget in nav bar
            cy.get('nav').contains('Budget').click()

            // Try to submit without amount
            cy.contains('button', 'Set Budget').click()
            cy.contains('Please enter a budget amount').should('be.visible')

            // Try negative amount
            cy.get('input[name="amount"]').type('-100')
            cy.contains('button', 'Set Budget').click()
            cy.contains('Budget amount must be positive').should('be.visible')

            // Try zero
            cy.get('input[name="amount"]').clear().type('0')
            cy.contains('button', 'Set Budget').click()
            cy.contains('Budget amount must be greater than zero').should('be.visible')
        })

        it('should require period selection', () => {
            // Click on Budget in nav bar
            cy.get('nav').contains('Budget').click()

            // Enter valid amount but don't select period
            cy.get('input[name="amount"]').type('1000')
            cy.get('select[name="period"]').select('') // Clear selection if possible

            // Try to submit
            cy.contains('button', 'Set Budget').click()
            cy.contains('Please select a budget period').should('be.visible')
        })
    })
})
