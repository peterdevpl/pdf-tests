<?php
declare(strict_types=1);

namespace PeterDev\Invoices\Presentation\View;

use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\MoneyFormatter;
use PeterDev\Invoices\Domain\Invoice;
use PeterDev\Invoices\Domain\InvoiceItem;
use function Functional\map;

final class InvoiceView implements ViewInterface
{
    /** @var Invoice */
    private $invoice;

    /** @var MoneyFormatter */
    private $moneyFormatter;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->moneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());
    }

    public function getData(): array
    {
        return [
            'number' => $this->invoice->getNumber(),
            'sender' => $this->invoice->getSender(),
            'recipient' => $this->invoice->getRecipient(),
            'issueDate' => $this->invoice->getIssueDate()->format('Y-m-d'),
            'dueDate' => $this->invoice->getDueDate()->format('Y-m-d'),
            'bankAccountNumber' => $this->invoice->getBankAccountNumber(),
            'items' => $this->getItemsData(),
            'totalNetAmount' => $this->moneyFormatter->format($this->invoice->getTotalNetAmount()),
            'totalVatAmount' => $this->moneyFormatter->format($this->invoice->getTotalVatAmount()),
            'totalGrossAmount' => $this->moneyFormatter->format($this->invoice->getTotalGrossAmount()),
        ];
    }

    private function getItemsData(): array
    {
        return map($this->invoice->getItems(), function(InvoiceItem $item) {
            return [
                'name' => $item->getName(),
                'quantity' => $item->getQuantity(),
                'netPrice' => $this->moneyFormatter->format($item->getNetPrice()),
                'vatRate' => $item->getVatRate(),
                'vatAmount' => $this->moneyFormatter->format($item->getVatAmount()),
                'grossAmount' => $this->moneyFormatter->format($item->getGrossAmount()),
            ];
        });
    }
}
