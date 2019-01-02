<?php
declare(strict_types=1);

namespace PeterDev\Invoices\Domain;

final class Company
{
    /** @var string */
    private $name;

    /** @var string */
    private $vatId;

    /** @var string */
    private $street;

    /** @var string */
    private $postalCode;

    /** @var string */
    private $city;

    public function __construct(string $name, string $vatId, string $street, string $postalCode, string $city)
    {
        $this->name = $name;
        $this->vatId = $vatId;
        $this->street = $street;
        $this->postalCode = $postalCode;
        $this->city = $city;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVatId(): string
    {
        return $this->vatId;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }
}
