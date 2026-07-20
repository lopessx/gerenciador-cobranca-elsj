<?php

namespace App\Payment;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class GatewayRegistry
{
    /** @var array<string, PaymentGatewayInterface> */
    private array $gateways = [];

    public function __construct(
        #[TaggedIterator('app.payment_gateway')]
        iterable $gateways,
    ) {
        foreach ($gateways as $gateway) {
            $this->gateways[$gateway->getName()] = $gateway;
        }
    }

    public function get(string $name): ?PaymentGatewayInterface
    {
        return $this->gateways[$name] ?? null;
    }

    /**
     * @return array<string, PaymentGatewayInterface>
     */
    public function getAll(): array
    {
        return $this->gateways;
    }
}
