describe('Jegyfoglaló felület', () => {
  it('betölti a főoldalt', () => {
    cy.visit('/')
    cy.contains('h1', 'Welcome to TicketMaster!')
    cy.contains('a', 'Buy Tickets').should('be.visible')
  })
})
