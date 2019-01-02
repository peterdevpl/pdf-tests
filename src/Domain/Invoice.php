<?php
declare(strict_types=1);

namespace PeterDev\Invoices\Domain;

use Money\Money;
use function Functional\reduce_left;

final class Invoice
{
    /** @var string */
    private $number;

    /** @var \DateTimeImmutable */
    private $issueDate;

    /** @var \DateTimeImmutable */
    private $dueDate;

    /** @var Company */
    private $sender;

    /** @var Company */
    private $recipient;

    /** @var string */
    private $bankAccountNumber = '';

    private $items = [];

    public function __construct(
        string $number,
        Company $sender,
        Company $recipient,
        \DateTimeImmutable $issueDate,
        \DateTimeImmutable $dueDate
    ) {
        $this->number = $number;
        $this->sender = $sender;
        $this->recipient = $recipient;
        $this->issueDate = $issueDate;
        $this->dueDate = $dueDate;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getSender(): Company
    {
        return $this->sender;
    }

    public function getRecipient(): Company
    {
        return $this->recipient;
    }

    public function getIssueDate(): \DateTimeImmutable
    {
        return $this->issueDate;
    }

    public function getDueDate(): \DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function getBankAccountNumber(): string
    {
        return $this->bankAccountNumber;
    }

    public function setBankAccountNumber(string $number): self
    {
        $this->bankAccountNumber = $number;

        return $this;
    }

    public function addItem(string $name, int $quantity, Money $netPrice, int $vatRate): self
    {
        $this->items[] = new InvoiceItem($name, $quantity, $netPrice, $vatRate);

        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalNetAmount(): Money
    {
        return reduce_left($this->items, function(InvoiceItem $item, int $index, array $collection, ?Money $initial) {
            if (null === $initial) {
                return $item->getNetAmount();
            }

            return $initial->add($item->getNetAmount());
        });
    }

    public function getTotalVatAmount(): Money
    {
        return reduce_left($this->items, function(InvoiceItem $item, int $index, array $collection, ?Money $initial) {
            if (null === $initial) {
                return $item->getVatAmount();
            }

            return $initial->add($item->getVatAmount());
        });
    }

    public function getTotalGrossAmount(): Money
    {
        return reduce_left($this->items, function(InvoiceItem $item, int $index, array $collection, ?Money $initial) {
            if (null === $initial) {
                return $item->getGrossAmount();
            }

            return $initial->add($item->getGrossAmount());
        });
    }
}
