<?php
declare(strict_types=1);

namespace PeterDev\Invoices\Domain;

use Money\Money;
use function Functional\reduce_left;

final class Invoice
{
    /** @var string */
    private $number;

    private $issueDate;

    private $dueDate;

    private $sender;

    private $recipient;

    private $items = [];

    public function __construct(string $number)
    {
        $this->number = $number;
    }

    public function getNumber(): string
    {
        return $this->number;
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
