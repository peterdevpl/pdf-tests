Feature: PDF rendition of an invoice
  In order to send an invoice to an accountant
  As a customer
  I need to download a PDF rendition of an invoice that complies with the tax law

  Scenario: Domestic invoice with two products having different VAT rates
    Given there is a domestic invoice with number "123/2018"
    And it contains an item "First product" with 100.00 EUR net price and 8% VAT
    And it contains an item "Second product" with 50.00 EUR net price and 23% VAT
    When I generate a PDF file for that invoice
    Then I should have a PDF file with 1 page in A4 portrait
    And it should contain correct sender data with its name, VAT ID and address
    And it should contain correct recipient data with its name, VAT ID and address
    And it should contain correct issue and due dates
    And the total net price should be 150.00 EUR
    And the VAT amount should be 19.50 EUR
    And the total gross price should be 169.50 EUR
    And the bank account number should be specified
