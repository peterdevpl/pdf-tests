<?php
declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use mikehaertl\pdftk\Pdf;
use Money\Currency;
use Money\Money;
use PeterDev\Invoices\Domain\Invoice;
use PeterDev\Invoices\Presentation\View\InvoiceViewRenderer;
use PHPUnit\Framework\Assert;
use SGH\PdfBox\PdfBox;

/**
 * Defines application features from the specific context.
 */
final class FeatureContext implements Context
{
    /** @var Invoice */
    private $invoice;

    /** @var string */
    private $plainText;

    /** @var string */
    private $metadata;

    private $pageDimensions = [
        'A4' => ['portrait' => '594.96 841.92', 'landscape' => '841.92 594.96'],
    ];

    /**
     * @Given there is a domestic invoice with number :number
     */
    public function thereIsADomesticInvoiceWithNumber(string $number): void
    {
        $this->invoice = new Invoice($number);
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
     * @When I generate a PDF file for invoice :number
     */
    public function iGenerateAPDFFileForInvoice(string $number): void
    {
        $renderer = new InvoiceViewRenderer();
        $html = $renderer->render($this->invoice);

        /* TODO: launch Chrome in headless mode to render the PDF file */
    }

    /**
     * @Then /I should have a PDF file with (\d+) pages? in ([A-Z]\d) (portrait|landscape)/
     */
    public function iShouldHaveAPDFFileWithPageIn(int $pagesCount, string $pageFormat, string $orientation): void
    {
        $pdfPath = __DIR__ . '/../../invoice.html.pdf';

        $converter = new PdfBox();
        $converter->setPathToPdfBox(__DIR__ . '/../../pdfbox-app.jar');
        $this->plainText = $converter->textFromPdfFile($pdfPath);

        $pdfReader = new Pdf($pdfPath);
        $this->metadata = $pdfReader->getData();

        Assert::assertNotEmpty($this->plainText);
        Assert::assertContains('NumberOfPages: ' . $pagesCount, $this->metadata);
        Assert::assertContains(
            'PageMediaDimensions: ' . $this->pageDimensions[$pageFormat][$orientation],
            $this->metadata
        );
    }

    /**
     * @Then /the total net price should be (\d+\.\d+) ([A-Z]{3})/
     */
    public function totalNetPriceShouldBe(string $price, string $currency)
    {
        Assert::assertContains('Total ' . $price, $this->plainText);
    }

    /**
     * @Then /the VAT amount should be (\d+\.\d+) ([A-Z]{3})/
     */
    public function vatAmountShouldBe(string $price, string $currency)
    {
        Assert::assertEquals(1, \preg_match('/Total \d+\.\d+ ' . \str_replace('.', '\\.', $price) . '/', $this->plainText));
    }

    /**
     * @Then /the total gross price should be (\d+\.\d+) ([A-Z]{3})/
     */
    public function totalGrossPriceShouldBe(string $price, string $currency)
    {
        Assert::assertEquals(1, \preg_match('/Total \d+\.\d+ \d+\.\d+ ' . \str_replace('.', '\\.', $price) . '/', $this->plainText));
    }
}
