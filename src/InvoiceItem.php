<?php

declare(strict_types=1);

namespace FelixMuhoro\MpesaInvoices;

final readonly class InvoiceItem
{
    public function __construct(
        public string $description,
        public int|float $quantity,
        public float $unit_price,
        public float $amount,
    ) {}

    /**
     * Create an InvoiceItem from a plain array.
     *
     * @param  array{description:string, quantity?:int|float, unit_price?:float, amount?:float}  $data
     */
    public static function fromArray(array $data): self
    {
        $quantity   = $data['quantity']   ?? 1;
        $unit_price = $data['unit_price'] ?? ($data['amount'] ?? 0);
        $amount     = $data['amount']     ?? ($unit_price * $quantity);

        return new self(
            description: $data['description'],
            quantity:    $quantity,
            unit_price:  (float) $unit_price,
            amount:      (float) $amount,
        );
    }

    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'quantity'    => $this->quantity,
            'unit_price'  => $this->unit_price,
            'amount'      => $this->amount,
        ];
    }
}
