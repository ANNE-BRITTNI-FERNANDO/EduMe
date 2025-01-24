describe('Donation Management', () => {
  let donorEmail;

  beforeEach(() => {
    // Register and login as a donor
    cy.visit('/register')
    cy.get('#name').type('Test Donor')
    const timestamp = new Date().getTime()
    donorEmail = `donor${timestamp}@test.com`
    cy.get('#email').type(donorEmail)
    cy.get('#location').should('be.visible').select('Colombo')
    cy.get('#password').type('password123')
    cy.get('#password_confirmation').type('password123')
    cy.get('button[type="submit"]').click()

    // Verify registration and redirect
    cy.url().should('include', '/dashboard1')
  })

  it('should create a donation', () => {
    // Navigate to donation page
    cy.contains('Donate').click()
    cy.url().should('include', '/donor')

    // Click on Donate Educational Items
    cy.contains('Donate Items Now').click()

    // Fill in donation details
    const timestamp = new Date().getTime()
    const itemName = `Test Book ${timestamp}`
    
    cy.get('#item_name').type(itemName)
    cy.get('#education_level').select('undergraduate')
    cy.get('#category').select('textbooks')
    cy.get('#quantity').type('1')
    cy.get('#description').type('A test book for donation')
    cy.get('#condition').select('like_new')
    
    // Fill in contact information
    cy.get('#contact_number').type('1234567890')
    cy.get('#pickup_address').type('123 Test Street, Test City')
    cy.get('#preferred_contact_method').select('phone')
    cy.get('[name="preferred_contact_times[]"][value="morning"]').check()
    cy.get('[name="preferred_contact_times[]"][value="afternoon"]').check()
    
    // Upload images
    cy.get('#images').selectFile('cypress/fixtures/product-image.jpg')
    
    // Submit form
    cy.get('button[type="submit"]').click()

    // Wait for success message with longer timeout
    cy.contains('Donation submitted successfully! It will be reviewed by our team.', { timeout: 10000 }).should('be.visible')

    // Visit donation history page directly
    cy.visit('/donations/history')

    // Verify item appears in donation history
    cy.contains(itemName).should('be.visible')
  })

  it('should handle donation approval flow', () => {
    // First create a donation as donor
    cy.contains('Donate').click()
    cy.contains('Donate Items Now').click()

    const timestamp = new Date().getTime()
    const itemName = `Test Book ${timestamp}`
    
    cy.get('#item_name').type(itemName)
    cy.get('#education_level').select('undergraduate')
    cy.get('#category').select('textbooks')
    cy.get('#quantity').type('1')
    cy.get('#description').type('A test book for donation')
    cy.get('#condition').select('like_new')
    cy.get('#contact_number').type('1234567890')
    cy.get('#pickup_address').type('123 Test Street, Test City')
    cy.get('#preferred_contact_method').select('phone')
    cy.get('[name="preferred_contact_times[]"][value="morning"]').check()
    cy.get('[name="preferred_contact_times[]"][value="afternoon"]').check()
    cy.get('#images').selectFile('cypress/fixtures/product-image.jpg')
    cy.get('button[type="submit"]').click()

    // Verify success message
    cy.contains('Donation submitted successfully! It will be reviewed by our team.', { timeout: 10000 }).should('be.visible')

    // Logout as donor
    cy.get('button').contains('Test Donor').click()
    cy.contains('Log Out').click()

    // Login as admin
    cy.visit('/login')
    cy.get('#email').type('admin@gmail.com')
    cy.get('#password').type('admin123')
    cy.get('button[type="submit"]').click()

    // Navigate to pending donations tab directly
    cy.visit('/admin/donations?tab=pending')

    // Find the donation and click View Details
    cy.contains(itemName)
      .parents('.bg-white')
      .first()
      .within(() => {
        cy.get('button[onclick^="toggleDetails"]').click()
      })

    // Wait for details section to be visible and approve
    cy.contains('button', 'Approve').click()

    // Switch to approved tab and verify donation is there
    cy.visit('/admin/donations?tab=approved')
    
    // Wait for the approved tab to load and verify donation is there
    cy.contains('h3', itemName).should('be.visible')
    cy.contains('.bg-white', itemName).within(() => {
      cy.contains('Approved').should('be.visible')
    })

  })

  it('should handle donation request flow (pending)', () => {
        // First create a donation as donor
        cy.contains('Donate').click()
        cy.contains('Donate Items Now').click()
    
        const timestamp = new Date().getTime()
        const itemName = `Test Book ${timestamp}`
        
        cy.get('#item_name').type(itemName)
        cy.get('#education_level').select('undergraduate')
        cy.get('#category').select('textbooks')
        cy.get('#quantity').type('1')
        cy.get('#description').type('A test book for donation')
        cy.get('#condition').select('like_new')
        cy.get('#contact_number').type('1234567890')
        cy.get('#pickup_address').type('123 Test Street, Test City')
        cy.get('#preferred_contact_method').select('phone')
        cy.get('[name="preferred_contact_times[]"][value="morning"]').check()
        cy.get('[name="preferred_contact_times[]"][value="afternoon"]').check()
        cy.get('#images').selectFile('cypress/fixtures/product-image.jpg')
        cy.get('button[type="submit"]').click()
    
        // Verify success message
        cy.contains('Donation submitted successfully! It will be reviewed by our team.', { timeout: 10000 }).should('be.visible')
    
        // Logout as donor
        cy.get('button').contains('Test Donor').click()
        cy.contains('Log Out').click()
    
        // Login as admin
        cy.visit('/login')
        cy.get('#email').type('admin@gmail.com')
        cy.get('#password').type('admin123')
        cy.get('button[type="submit"]').click()
    
        // Navigate to pending donations tab directly
        cy.visit('/admin/donations?tab=pending')
    
        // Find the donation and click View Details
        cy.contains(itemName)
          .parents('.bg-white')
          .first()
          .within(() => {
            cy.get('button[onclick^="toggleDetails"]').click()
          })
    
        // Wait for details section to be visible and approve
        cy.contains('button', 'Approve').click()
    
        // Switch to approved tab and verify donation is there
        cy.visit('/admin/donations?tab=approved')
        
        // Wait for the approved tab to load and verify donation is there
        cy.contains('h3', itemName).should('be.visible')
        cy.contains('.bg-white', itemName).within(() => {
          cy.contains('Approved').should('be.visible')
        })

        // Logout as admin
        cy.get('button').contains('Admin').click()
        cy.contains('Log Out').click()

        // Register and login as student
        cy.visit('/register')
        cy.get('#name').type('Test Student')
        cy.get('#email').type(`student${timestamp}@test.com`)
        cy.get('#location').select('Colombo')
        cy.get('#password').type('password123')
        cy.get('#password_confirmation').type('password123')
        cy.get('button[type="submit"]').click()

        // Navigate to Browse Items page
        cy.visit('/donations/available')

        // Find and request the donation in the available items grid
        cy.contains('.bg-white', itemName)
          .within(() => {
            cy.contains('Request Item').click()
          })

        // Fill in request details
        cy.get('#document_type').select('student_id')
        cy.get('#verification_document').selectFile('cypress/fixtures/product-image.jpg')
        cy.get('#quantity').type('1')
        cy.get('#purpose').select('educational')
        cy.get('#contact_number').type('1234567890')
        cy.get('#notes').type('I will take good care of the item')

        // Submit the request
        cy.contains('button', 'Submit Request').click()

        // Click Back to Dashboard button
        cy.contains('Back to Dashboard').click()

        // Check request is in pending tab
        cy.get('#pending-tab').click()
        cy.contains(itemName).should('be.visible')
        cy.contains('Pending').should('be.visible')

  })

  it('should handle donation request flow (approved)', () => {
    // First create a donation as donor
    cy.contains('Donate').click()
    cy.contains('Donate Items Now').click()

    const timestamp = new Date().getTime()
    const itemName = `Test Book ${timestamp}`
    
    cy.get('#item_name').type(itemName)
    cy.get('#education_level').select('undergraduate')
    cy.get('#category').select('textbooks')
    cy.get('#quantity').type('1')
    cy.get('#description').type('A test book for donation')
    cy.get('#condition').select('like_new')
    cy.get('#contact_number').type('1234567890')
    cy.get('#pickup_address').type('123 Test Street, Test City')
    cy.get('#preferred_contact_method').select('phone')
    cy.get('[name="preferred_contact_times[]"][value="morning"]').check()
    cy.get('[name="preferred_contact_times[]"][value="afternoon"]').check()
    cy.get('#images').selectFile('cypress/fixtures/product-image.jpg')
    cy.get('button[type="submit"]').click()

    // Verify success message
    cy.contains('Donation submitted successfully! It will be reviewed by our team.', { timeout: 10000 }).should('be.visible')

    // Logout as donor
    cy.get('button').contains('Test Donor').click()
    cy.contains('Log Out').click()

    // Login as admin
    cy.visit('/login')
    cy.get('#email').type('admin@gmail.com')
    cy.get('#password').type('admin123')
    cy.get('button[type="submit"]').click()

    // Navigate to pending donations tab directly
    cy.visit('/admin/donations?tab=pending')

    // Find the donation and click View Details
    cy.contains(itemName)
      .parents('.bg-white')
      .first()
      .within(() => {
        cy.get('button[onclick^="toggleDetails"]').click()
      })

    // Wait for details section to be visible and approve
    cy.contains('button', 'Approve').click()

    // Switch to approved tab and verify donation is there
    cy.visit('/admin/donations?tab=approved')
    
    // Wait for the approved tab to load and verify donation is there
    cy.contains('h3', itemName).should('be.visible')
    cy.contains('.bg-white', itemName).within(() => {
      cy.contains('Approved').should('be.visible')
    })

    // Logout as admin
    cy.get('button').contains('Admin').click()
    cy.contains('Log Out').click()

    // Register and login as student
    cy.visit('/register')
    cy.get('#name').type('Test Student')
    cy.get('#email').type(`student${timestamp}@test.com`)
    cy.get('#location').select('Colombo')
    cy.get('#password').type('password123')
    cy.get('#password_confirmation').type('password123')
    cy.get('button[type="submit"]').click()

    // Navigate to Browse Items page
    cy.visit('/donations/available')

    // Find and request the donation in the available items grid
    cy.contains('.bg-white', itemName)
      .within(() => {
        cy.contains('Request Item').click()
      })

    // Fill in request details
    cy.get('#document_type').select('student_id')
    cy.get('#verification_document').selectFile('cypress/fixtures/product-image.jpg')
    cy.get('#quantity').type('1')
    cy.get('#purpose').select('educational')
    cy.get('#contact_number').type('1234567890')
    cy.get('#notes').type('I will take good care of the item')

    // Submit the request
    cy.contains('button', 'Submit Request').click()

    // Click Back to Dashboard button
    cy.contains('Back to Dashboard').click()

    // Check request is in pending tab
    cy.get('#pending-tab').click()
    cy.contains(itemName).should('be.visible')
    cy.contains('Pending').should('be.visible')

    // Logout as student
    cy.get('button').contains('Test Student').click()
    cy.contains('Log Out').click()

    //login as an admin
    cy.visit('/login')
    cy.get('#email').type('admin@gmail.com')
    cy.get('#password').type('admin123')
    cy.get('button[type="submit"]').click()

    // Navigate to donation requests
    cy.contains('Manage Donations').click()
    cy.contains('View Donation Requests').click()

    // Find and approve the request in pending requests
    cy.contains(itemName)
      .parents('.bg-white')
      .within(() => {
        cy.contains('Approve').click()
      })

    // Go to approved requests tab and verify
    cy.contains('Approved').click()
    cy.contains(itemName).should('be.visible')

    // Logout as admin
    cy.get('button').contains('Admin').click()
    cy.contains('Log Out').click()

    // Login back as student
    cy.visit('/login')
    cy.get('#email').type(`student${timestamp}@test.com`)
    cy.get('#password').type('password123')
    cy.get('button[type="submit"]').click()

    // Go to My Requests and check approved tab
    cy.contains('Donate').click()
    cy.get('#approved-tab').click()
    cy.contains(itemName).should('be.visible')
    cy.contains('Approved').should('be.visible')
  })

  it('should handle donation item rejection', () => {
    // First create a donation as donor
    cy.contains('Donate').click()
    cy.contains('Donate Items Now').click()

    const timestamp = new Date().getTime()
    const itemName = `Test Book ${timestamp}`
    
    cy.get('#item_name').type(itemName)
    cy.get('#education_level').select('undergraduate')
    cy.get('#category').select('textbooks')
    cy.get('#quantity').type('1')
    cy.get('#description').type('A test book for donation')
    cy.get('#condition').select('like_new')
    cy.get('#contact_number').type('1234567890')
    cy.get('#pickup_address').type('123 Test Street, Test City')
    cy.get('#preferred_contact_method').select('phone')
    cy.get('[name="preferred_contact_times[]"][value="morning"]').check()
    cy.get('[name="preferred_contact_times[]"][value="afternoon"]').check()
    cy.get('#images').selectFile('cypress/fixtures/product-image.jpg')
    cy.get('button[type="submit"]').click()

    // Verify success message
    cy.contains('Donation submitted successfully! It will be reviewed by our team.', { timeout: 10000 }).should('be.visible')

    // Logout as donor
    cy.get('button').contains('Test Donor').click()
    cy.contains('Log Out').click()

    // Login as admin
    cy.visit('/login')
    cy.get('#email').type('admin@gmail.com')
    cy.get('#password').type('admin123')
    cy.get('button[type="submit"]').click()

    // Navigate to pending donations tab directly
    cy.visit('/admin/donations?tab=pending')

    // Find the donation and click View Details
    cy.contains(itemName)
      .parents('.bg-white')
      .first()
      .within(() => {
        // First click View Details
        cy.contains('View Details').click()
        // Then click Reject
        cy.contains('button', 'Reject').click()
      })


    // Switch to rejected tab and verify donation is there
    cy.visit('/admin/donations?tab=rejected')
    
    // Wait for the approved tab to load and verify donation is there
    cy.contains('h3', itemName).should('be.visible')
    cy.contains('.bg-white', itemName).within(() => {
      cy.contains('Rejected').should('be.visible')
    })

    // Logout as admin
    cy.get('button').contains('Admin').click()
    cy.contains('Log Out').click()

  })

  it('should handle donation request (rejected)', () => {
        // First create a donation as donor
    cy.contains('Donate').click()
    cy.contains('Donate Items Now').click()

    const timestamp = new Date().getTime()
    const itemName = `Test Book ${timestamp}`
    
    cy.get('#item_name').type(itemName)
    cy.get('#education_level').select('undergraduate')
    cy.get('#category').select('textbooks')
    cy.get('#quantity').type('1')
    cy.get('#description').type('A test book for donation')
    cy.get('#condition').select('like_new')
    cy.get('#contact_number').type('1234567890')
    cy.get('#pickup_address').type('123 Test Street, Test City')
    cy.get('#preferred_contact_method').select('phone')
    cy.get('[name="preferred_contact_times[]"][value="morning"]').check()
    cy.get('[name="preferred_contact_times[]"][value="afternoon"]').check()
    cy.get('#images').selectFile('cypress/fixtures/product-image.jpg')
    cy.get('button[type="submit"]').click()

    // Verify success message
    cy.contains('Donation submitted successfully! It will be reviewed by our team.', { timeout: 10000 }).should('be.visible')

    // Logout as donor
    cy.get('button').contains('Test Donor').click()
    cy.contains('Log Out').click()

    // Login as admin
    cy.visit('/login')
    cy.get('#email').type('admin@gmail.com')
    cy.get('#password').type('admin123')
    cy.get('button[type="submit"]').click()

    // Navigate to pending donations tab directly
    cy.visit('/admin/donations?tab=pending')

    // Find the donation and click View Details
    cy.contains(itemName)
      .parents('.bg-white')
      .first()
      .within(() => {
        cy.get('button[onclick^="toggleDetails"]').click()
      })

    // Wait for details section to be visible and approve
    cy.contains('button', 'Approve').click()

    // Switch to approved tab and verify donation is there
    cy.visit('/admin/donations?tab=approved')
    
    // Wait for the approved tab to load and verify donation is there
    cy.contains('h3', itemName).should('be.visible')
    cy.contains('.bg-white', itemName).within(() => {
      cy.contains('Approved').should('be.visible')
    })

    // Logout as admin
    cy.get('button').contains('Admin').click()
    cy.contains('Log Out').click()

    // Register and login as student
    cy.visit('/register')
    cy.get('#name').type('Test Student')
    cy.get('#email').type(`student${timestamp}@test.com`)
    cy.get('#location').select('Colombo')
    cy.get('#password').type('password123')
    cy.get('#password_confirmation').type('password123')
    cy.get('button[type="submit"]').click()

    // Navigate to Browse Items page
    cy.visit('/donations/available')

    // Find and request the donation in the available items grid
    cy.contains('.bg-white', itemName)
      .within(() => {
        cy.contains('Request Item').click()
      })

    // Fill in request details
    cy.get('#document_type').select('student_id')
    cy.get('#verification_document').selectFile('cypress/fixtures/product-image.jpg')
    cy.get('#quantity').type('1')
    cy.get('#purpose').select('educational')
    cy.get('#contact_number').type('1234567890')
    cy.get('#notes').type('I will take good care of the item')

    // Submit the request
    cy.contains('button', 'Submit Request').click()

    // Click Back to Dashboard button
    cy.contains('Back to Dashboard').click()

    // Check request is in pending tab
    cy.get('#pending-tab').click()
    cy.contains(itemName).should('be.visible')
    cy.contains('Pending').should('be.visible')

    // Logout as student
    cy.get('button').contains('Test Student').click()
    cy.contains('Log Out').click()

    //login as an admin
    cy.visit('/login')
    cy.get('#email').type('admin@gmail.com')
    cy.get('#password').type('admin123')
    cy.get('button[type="submit"]').click()

    // Navigate to donation requests
    cy.contains('Manage Donations').click()
    cy.contains('View Donation Requests').click()

    // Find and reject the request in pending requests
    cy.contains(itemName)
      .parents('.bg-white')
      .within(() => {
        cy.contains('Reject').click()
      })

    // State the reason for rejection in the modal
    cy.get('#rejection_reason')
      .should('be.visible')
      .type('This item is not available')

    // Click the Reject button in the modal specifically
    cy.get('#reject-modal')
      .find('button')
      .contains('Reject')
      .click()

    // Go to rejected requests tab and verify
    cy.contains('Rejected').click()
    cy.contains(itemName).should('be.visible')

    // Logout as admin
    cy.get('button').contains('Admin').click()
    cy.contains('Log Out').click()

    // Login back as student
    cy.visit('/login')
    cy.get('#email').type(`student${timestamp}@test.com`)
    cy.get('#password').type('password123')
    cy.get('button[type="submit"]').click()

    // Go to My Requests and check approved tab
    cy.contains('Donate').click()
    cy.get('#rejected-tab').click()
    cy.contains(itemName).should('be.visible')
    cy.contains('Rejected').should('be.visible')

  })

    it('should handle donation request flow received by donor', () => {
    // First create a donation as donor
    cy.contains('Donate').click()
    cy.contains('Donate Items Now').click()

    const timestamp = new Date().getTime()
    const itemName = `Test Book ${timestamp}`
    
    cy.get('#item_name').type(itemName)
    cy.get('#education_level').select('undergraduate')
    cy.get('#category').select('textbooks')
    cy.get('#quantity').type('1')
    cy.get('#description').type('A test book for donation')
    cy.get('#condition').select('like_new')
    cy.get('#contact_number').type('1234567890')
    cy.get('#pickup_address').type('123 Test Street, Test City')
    cy.get('#preferred_contact_method').select('phone')
    cy.get('[name="preferred_contact_times[]"][value="morning"]').check()
    cy.get('[name="preferred_contact_times[]"][value="afternoon"]').check()
    cy.get('#images').selectFile('cypress/fixtures/product-image.jpg')
    cy.get('button[type="submit"]').click()

    // Verify success message
    cy.contains('Donation submitted successfully! It will be reviewed by our team.', { timeout: 10000 }).should('be.visible')

    // Logout as donor
    cy.get('button').contains('Test Donor').click()
    cy.contains('Log Out').click()

    // Login as admin
    cy.visit('/login')
    cy.get('#email').type('admin@gmail.com')
    cy.get('#password').type('admin123')
    cy.get('button[type="submit"]').click()

    // Navigate to pending donations tab directly
    cy.visit('/admin/donations?tab=pending')

    // Find the donation and click View Details
    cy.contains(itemName)
      .parents('.bg-white')
      .first()
      .within(() => {
        cy.get('button[onclick^="toggleDetails"]').click()
      })

    // Wait for details section to be visible and approve
    cy.contains('button', 'Approve').click()

    // Switch to approved tab and verify donation is there
    cy.visit('/admin/donations?tab=approved')
    
    // Wait for the approved tab to load and verify donation is there
    cy.contains('h3', itemName).should('be.visible')
    cy.contains('.bg-white', itemName).within(() => {
      cy.contains('Approved').should('be.visible')
    })

    // Logout as admin
    cy.get('button').contains('Admin').click()
    cy.contains('Log Out').click()

    // Register and login as student
    cy.visit('/register')
    cy.get('#name').type('Test Student')
    cy.get('#email').type(`student${timestamp}@test.com`)
    cy.get('#location').select('Colombo')
    cy.get('#password').type('password123')
    cy.get('#password_confirmation').type('password123')
    cy.get('button[type="submit"]').click()

    // Navigate to Browse Items page
    cy.visit('/donations/available')

    // Find and request the donation in the available items grid
    cy.contains('.bg-white', itemName)
      .within(() => {
        cy.contains('Request Item').click()
      })

    // Fill in request details
    cy.get('#document_type').select('student_id')
    cy.get('#verification_document').selectFile('cypress/fixtures/product-image.jpg')
    cy.get('#quantity').type('1')
    cy.get('#purpose').select('educational')
    cy.get('#contact_number').type('1234567890')
    cy.get('#notes').type('I will take good care of the item')

    // Submit the request
    cy.contains('button', 'Submit Request').click()

    // Click Back to Dashboard button
    cy.contains('Back to Dashboard').click()

    // Check request is in pending tab
    cy.get('#pending-tab').click()
    cy.contains(itemName).should('be.visible')
    cy.contains('Pending').should('be.visible')

    // Logout as student
    cy.get('button').contains('Test Student').click()
    cy.contains('Log Out').click()

    //login as an admin
    cy.visit('/login')
    cy.get('#email').type('admin@gmail.com')
    cy.get('#password').type('admin123')
    cy.get('button[type="submit"]').click()

    // Navigate to donation requests
    cy.contains('Manage Donations').click()
    cy.contains('View Donation Requests').click()

    // Find and approve the request in pending requests
    cy.contains(itemName)
      .parents('.bg-white')
      .within(() => {
        cy.contains('Approve').click()
      })

    // Go to approved requests tab and verify
    cy.contains('Approved').click()
    cy.contains(itemName).should('be.visible')

    // Logout as admin
    cy.get('button').contains('Admin').click()
    cy.contains('Log Out').click()

    // Login back as the original donor
    cy.visit('/login')
    cy.get('#email').type(donorEmail)  
    cy.get('#password').type('password123')
    cy.get('button[type="submit"]').click()

    // Navigate to My Donations using Donate menu
    cy.contains('Donate').click()
    cy.get('#received-tab').click()
    cy.contains(itemName).should('be.visible')
    cy.contains('Approved').should('be.visible')
    
  })

  it('should be able to chat with recipient', () => {
    // First create a donation as donor
    cy.contains('Donate').click()
    cy.contains('Donate Items Now').click()

    const timestamp = new Date().getTime()
    const itemName = `Test Book ${timestamp}`
    
    cy.get('#item_name').type(itemName)
    cy.get('#education_level').select('undergraduate')
    cy.get('#category').select('textbooks')
    cy.get('#quantity').type('1')
    cy.get('#description').type('A test book for donation')
    cy.get('#condition').select('like_new')
    cy.get('#contact_number').type('1234567890')
    cy.get('#pickup_address').type('123 Test Street, Test City')
    cy.get('#preferred_contact_method').select('phone')
    cy.get('[name="preferred_contact_times[]"][value="morning"]').check()
    cy.get('[name="preferred_contact_times[]"][value="afternoon"]').check()
    cy.get('#images').selectFile('cypress/fixtures/product-image.jpg')
    cy.get('button[type="submit"]').click()

    // Verify success message
    cy.contains('Donation submitted successfully! It will be reviewed by our team.', { timeout: 10000 }).should('be.visible')

    // Logout as donor
    cy.get('button').contains('Test Donor').click()
    cy.contains('Log Out').click()

    // Login as admin
    cy.visit('/login')
    cy.get('#email').type('admin@gmail.com')
    cy.get('#password').type('admin123')
    cy.get('button[type="submit"]').click()

    // Navigate to pending donations tab directly
    cy.visit('/admin/donations?tab=pending')

    // Find the donation and click View Details
    cy.contains(itemName)
      .parents('.bg-white')
      .first()
      .within(() => {
        cy.get('button[onclick^="toggleDetails"]').click()
      })

    // Wait for details section to be visible and approve
    cy.contains('button', 'Approve').click()

    // Switch to approved tab and verify donation is there
    cy.visit('/admin/donations?tab=approved')
    
    // Wait for the approved tab to load and verify donation is there
    cy.contains('h3', itemName).should('be.visible')
    cy.contains('.bg-white', itemName).within(() => {
      cy.contains('Approved').should('be.visible')
    })

    // Logout as admin
    cy.get('button').contains('Admin').click()
    cy.contains('Log Out').click()

    // Register and login as student
    cy.visit('/register')
    cy.get('#name').type('Test Student')
    cy.get('#email').type(`student${timestamp}@test.com`)
    cy.get('#location').select('Colombo')
    cy.get('#password').type('password123')
    cy.get('#password_confirmation').type('password123')
    cy.get('button[type="submit"]').click()

    // Navigate to Browse Items page
    cy.visit('/donations/available')

    // Find and request the donation in the available items grid
    cy.contains('.bg-white', itemName)
      .within(() => {
        cy.contains('Request Item').click()
      })

    // Fill in request details
    cy.get('#document_type').select('student_id')
    cy.get('#verification_document').selectFile('cypress/fixtures/product-image.jpg')
    cy.get('#quantity').type('1')
    cy.get('#purpose').select('educational')
    cy.get('#contact_number').type('1234567890')
    cy.get('#notes').type('I will take good care of the item')

    // Submit the request
    cy.contains('button', 'Submit Request').click()

    // Click Back to Dashboard button
    cy.contains('Back to Dashboard').click()

    // Check request is in pending tab
    cy.get('#pending-tab').click()
    cy.contains(itemName).should('be.visible')
    cy.contains('Pending').should('be.visible')

    // Logout as student
    cy.get('button').contains('Test Student').click()
    cy.contains('Log Out').click()

    //login as an admin
    cy.visit('/login')
    cy.get('#email').type('admin@gmail.com')
    cy.get('#password').type('admin123')
    cy.get('button[type="submit"]').click()

    // Navigate to donation requests
    cy.contains('Manage Donations').click()
    cy.contains('View Donation Requests').click()

    // Find and approve the request in pending requests
    cy.contains(itemName)
      .parents('.bg-white')
      .within(() => {
        cy.contains('Approve').click()
      })

    // Go to approved requests tab and verify
    cy.contains('Approved').click()
    cy.contains(itemName).should('be.visible')

    // Logout as admin
    cy.get('button').contains('Admin').click()
    cy.contains('Log Out').click()

    // Login back as the original donor
    cy.visit('/login')
    cy.get('#email').type(donorEmail)  
    cy.get('#password').type('password123')
    cy.get('button[type="submit"]').click()

    // Navigate to My Donations using Donate menu
    cy.contains('Donate').click()
    cy.get('#received-tab').click()
    cy.contains(itemName).should('be.visible')
    cy.contains('Approved').should('be.visible')

    // chat with recipient
    cy.contains('Chat with Recipient').click()

    // Wait for chat interface to load and be visible
    cy.get('input[placeholder="Type your message..."]')
      .should('be.visible')
      .type('Hi!')

    // Click the purple SEND button
    cy.get('button.bg-indigo-600')
      .should('be.visible')
      .click()

    // Verify message appears
    cy.contains('Hi!').should('be.visible')
    
  })

  it('should handle donation item removal', () => {
    // First create a donation as donor
    cy.contains('Donate').click()
    cy.contains('Donate Items Now').click()

    const timestamp = new Date().getTime()
    const itemName = `Test Book ${timestamp}`
    
    cy.get('#item_name').type(itemName)
    cy.get('#education_level').select('undergraduate')
    cy.get('#category').select('textbooks')
    cy.get('#quantity').type('1')
    cy.get('#description').type('A test book for donation')
    cy.get('#condition').select('like_new')
    cy.get('#contact_number').type('1234567890')
    cy.get('#pickup_address').type('123 Test Street, Test City')
    cy.get('#preferred_contact_method').select('phone')
    cy.get('[name="preferred_contact_times[]"][value="morning"]').check()
    cy.get('[name="preferred_contact_times[]"][value="afternoon"]').check()
    cy.get('#images').selectFile('cypress/fixtures/product-image.jpg')
    cy.get('button[type="submit"]').click()

    // Verify success message
    cy.contains('Donation submitted successfully! It will be reviewed by our team.', { timeout: 10000 }).should('be.visible')

    // Logout as donor
    cy.get('button').contains('Test Donor').click()
    cy.contains('Log Out').click()

    // Login as admin
    cy.visit('/login')
    cy.get('#email').type('admin@gmail.com')
    cy.get('#password').type('admin123')
    cy.get('button[type="submit"]').click()

    // Navigate to pending donations tab directly
    cy.visit('/admin/donations?tab=pending')

    // Find the donation and click View Details
    cy.contains(itemName)
      .parents('.bg-white')
      .first()
      .within(() => {
        cy.get('button[onclick^="toggleDetails"]').click()
      })

    // Wait for details section to be visible and approve
    cy.contains('button', 'Approve').click()

    // Switch to approved tab and verify donation is there
    cy.visit('/admin/donations?tab=approved')
    
    // Wait for the approved tab to load and verify donation is there
    cy.contains('h3', itemName).should('be.visible')
    cy.contains('.bg-white', itemName).within(() => {
      cy.contains('Approved').should('be.visible')
    })

    // Click View Details to expand the donation details
    cy.contains(itemName)
      .parents('.bg-white')
      .first()
      .within(() => {
        cy.get('button[onclick^="toggleDetails"]').click()
      })

    // Click Remove from Available button
    cy.contains('button', 'Remove from Available').click()

    // Wait for the item to be removed and page to be updated
    cy.wait(1000) // Wait for the AJAX request to complete

    // Verify the item is no longer in the available list
    cy.reload()
    cy.contains(itemName).should('not.exist')
  })

})
