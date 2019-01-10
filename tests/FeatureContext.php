<?php
declare(strict_types=1);

namespace Test\PeterDev\Invoices;

use Behat\Behat\Context\Context;
use Money\Currency;
use Money\Money;
use NcJoes\PopplerPhp\Config;
use NcJoes\PopplerPhp\PdfInfo;
use PeterDev\Invoices\Domain\Company;
use PeterDev\Invoices\Domain\Invoice;
use PeterDev\Invoices\Presentation\InvoicePdfRenderer;
use PHPUnit\Framework\Assert;
use Spatie\PdfToText\Pdf;

final class FeatureContext implements Context
{
    /** @var Invoice */
    private $invoice;

    /** @var string */
    private $pdf;

    /** @var string */
    private $plainText;

    private $pageDimensions = [
        'A4' => ['portrait' => '594.96 x 841.92 pts (A4)', 'landscape' => '841.92 x 594.96 pts (A4)'],
    ];

    /**
     * @Given there is a domestic invoice with number :number
     */
    public function thereIsADomesticInvoiceWithNumber(string $number): void
    {
        $sender = new Company('Test Sender', 'PL1112223344', 'Grunwaldzka 1', '00-000', 'Warszawa');
        $recipient = new Company('Test Recipient', 'PL5556667788', 'Prosta 1', '00-000', 'Kraków');
        $issueDate = new \DateTimeImmutable();
        $dueDate = $issueDate->add(new \DateInterval('P14D'));
        $this->invoice = new Invoice($number, $sender, $recipient, $issueDate, $dueDate);
        $this->invoice->setBankAccountNumber('00 1111 2222 3333 4444 5555 6666');
    }

    /**
     * @Given /it contains an item \"([^\"]+)\" with (\d+\.\d+) ([A-Z]{3}) net price and (\d+)% VAT/
     */
    public function itContainsAnItemWithNetPriceAndVAT(string $name, string $price, string $currency, int $vatRate): void
    {
        $this->invoice->addItem(
            $name,
            1,
            new Money(\str_replace('.', '', $price), new Currency($currency)),
            $vatRate
        );
    }

    /**
     * @When I generate a PDF file for that invoice
     */
    public function iGenerateAPDFFileForThatInvoice(): void
    {
        $renderer = new InvoicePdfRenderer();
        $this->pdf = $renderer->render($this->invoice);
    }

    /**
     * @Then /I should have a PDF file with (\d+) pages? in ([A-Z]\d) (portrait|landscape)/
     */
    public function iShouldHaveAPDFFileWithPageIn(int $pagesCount, string $pageFormat, string $orientation): void
    {
        $temporary = \tempnam(\sys_get_temp_dir(), 'pdf');
        \file_put_contents($temporary, $this->pdf);

        $pdfToTextConverter = new Pdf();
        $pdfToTextConverter->setPdf($temporary);
        $this->plainText = $pdfToTextConverter->text();

        Config::setBinDirectory('/usr/bin/');
        $pdfInfo = new PdfInfo($temporary);

        Assert::assertNotEmpty($this->plainText);
        Assert::assertEquals($pagesCount, $pdfInfo->getNumOfPages());
        Assert::assertEquals(
            $this->pageDimensions[$pageFormat][$orientation],
            $pdfInfo->getPageSize()
        );

        \unlink($temporary);
    }

    /**
     * @Then it should contain correct sender data with its name, VAT ID and address
     */
    public function itShouldContainCorrectSenderData(): void
    {
        $data = "Seller\n" . $this->getTextCompanyData($this->invoice->getSender());
        Assert::assertContains($this->removeWhitespaces($data), $this->removeWhitespaces($this->plainText));
    }

    /**
     * @Then it should contain correct recipient data with its name, VAT ID and address
     */
    public function itShouldContainCorrectRecipientData(): void
    {
        $data = "Buyer\n" . $this->getTextCompanyData($this->invoice->getRecipient());
        Assert::assertContains($this->removeWhitespaces($data), $this->removeWhitespaces($this->plainText));
    }

    private function getTextCompanyData(Company $company): string
    {
        return $company->getName() . "\nVAT ID: " . $company->getVatId() . "\n" . $company->getStreet() . "\n" .
            $company->getPostalCode() . ' ' . $company->getCity();
    }

    /**
     * Sometimes a text dumped from a PDF file might contain additional whitespaces or different line ends.
     * We're removing all whitespaces to make multiline comparison less error-prone.
     */
    private function removeWhitespaces(string $string): string
    {
        return (string) \preg_replace('/\s+/m', '', $string);
    }

    /**
     * @Then it should contain correct issue and due dates
     */
    public function itShouldContainCorrectIssueAndDueDates(): void
    {
        $pattern = '/Issue date\s+' . $this->invoice->getIssueDate()->format('Y\-m\-d') .
            '\s+Due date\s+' . $this->invoice->getDueDate()->format('Y\-m\-d') . '/';
        Assert::assertEquals(1, \preg_match($pattern, $this->plainText));
    }

    /**
     * @Then /the total net price should be (\d+\.\d+) ([A-Z]{3})/
     */
    public function totalNetPriceShouldBe(string $price, string $currency): void
    {
        Assert::assertContains('Total ' . $price, $this->plainText);
    }

    /**
     * @Then /the VAT amount should be (\d+\.\d+) ([A-Z]{3})/
     */
    public function vatAmountShouldBe(string $price, string $currency): void
    {
        Assert::assertEquals(1, \preg_match('/Total \d+\.\d+ ' . \str_replace('.', '\\.', $price) . '/', $this->plainText));
    }

    /**
     * @Then /the total gross price should be (\d+\.\d+) ([A-Z]{3})/
     */
    public function totalGrossPriceShouldBe(string $price, string $currency): void
    {
        Assert::assertEquals(1, \preg_match('/Total \d+\.\d+ \d+\.\d+ ' . \str_replace('.', '\\.', $price) . '/', $this->plainText));
    }

    /**
     * @Then the bank account number should be specified
     */
    public function theBankAccountNumberShouldBeSpecified(): void
    {
        $pattern = '/Account number\s+' . $this->invoice->getBankAccountNumber() . '/';
        Assert::assertEquals(1, \preg_match($pattern, $this->plainText));
    }
}
