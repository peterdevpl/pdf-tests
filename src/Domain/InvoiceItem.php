<?php
declare(strict_types=1);

namespace PeterDev\Invoices\Domain;

use Money\Money;

final class InvoiceItem
{
    /** @var string */
    private $name;

    /** @var int */
    private $quantity;

    /** @var Money */
    private $netPrice;

    /** @var int */
    private $vatRate;

    /** @var Money */
    private $netAmount;

    /** @var Money */
    private $vatAmount;

    /** @var Money */
    private $grossAmount;

    public function __construct(string $name, int $quantity, Money $netPrice, int $vatRate)
    {
        if ($quantity < 1) {
            throw new \InvalidArgumentException(\sprintf('Wrong quantity: %d', $quantity));
        }
        if ($vatRate < 0 || $vatRate > 100) {
            throw new \InvalidArgumentException(\sprintf('Wrong VAT rate: %d', $vatRate));
        }

        $this->name = $name;
        $this->quantity = $quantity;
        $this->netPrice = $netPrice;
        $this->vatRate = $vatRate;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getNetPrice(): Money
    {
        return $this->netPrice;
    }

    public function getNetAmount(): Money
    {
        if (null === $this->netAmount) {
            $this->netAmount = $this->netPrice->multiply($this->quantity);
        }

        return $this->netAmount;
    }

    public function getVatRate(): int
    {
        return $this->vatRate;
    }

    public function getVatAmount(): Money
    {
        if (null === $this->vatAmount) {
            if (0 === $this->vatRate) {
                $this->vatAmount = new Money(0, $this->netPrice->getCurrency());
            } else {
                $vatMultiplier = '0.' . \str_pad((string) $this->vatRate, 2, '0', STR_PAD_LEFT);
                $this->vatAmount = $this->getNetAmount()->multiply($vatMultiplier);
            }
        }

        return $this->vatAmount;
    }

    public function getGrossAmount(): Money
    {
        if (null === $this->grossAmount) {
            if (0 === $this->vatRate) {
                $this->grossAmount = $this->getNetAmount();
            } else {
                $grossMultiplier = '1.' . \str_pad((string) $this->vatRate, 2, '0', STR_PAD_LEFT);
                $this->grossAmount = $this->getNetAmount()->multiply($grossMultiplier);
            }
        }

        return $this->grossAmount;
    }
}
