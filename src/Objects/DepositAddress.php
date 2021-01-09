<?php

namespace bitbuyAT\Bitstamp\Objects;

class DepositAddress
{
    /**
     * @var array
     */
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get Address.
     */
    public function getAddress(): string
    {
        return $this->data['address'];
    }

    /**
     * Whole data array.
     */
    public function getData(): array
    {
        return $this->data;
    }
}
