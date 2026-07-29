<?php

namespace App\Payment;

use App\Payment\Contract\PaymentGatewayInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class GatewayRegistry
{
    /**
     * @var array<string, PaymentGatewayInterface>
     */
    private array $gateways = [];

    /**
     * @param iterable<PaymentGatewayInterface> $gateways
     */
    public function __construct(
        #[TaggedIterator('app.payment_gateway')]
        iterable $gateways,
    ) {
        foreach ($gateways as $gateway) {
            $this->gateways[$gateway->getName()] = $gateway;
        }
    }

    /**
     * Retorna um gateway específico pelo nome.
     *
     * @throws \InvalidArgumentException se o gateway não for encontrado
     */
    public function get(string $name): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$name])) {
            throw new \InvalidArgumentException(
                sprintf("Gateway de pagamento '%s' não suportado. Disponíveis: %s", $name, implode(', ', array_keys($this->gateways)))
            );
        }

        return $this->gateways[$name];
    }

    /**
     * Retorna todos os gateways registrados.
     *
     * @return array<string, PaymentGatewayInterface>
     */
    public function all(): array
    {
        return $this->gateways;
    }

    /**
     * Retorna os schemas de todos os gateways para o front-end montar formulários dinâmicos.
     */
    public function getSchemas(): array
    {
        $schemas = [];

        foreach ($this->gateways as $gateway) {
            $schemas[] = [
                'name' => $gateway->getName(),
                'label' => $gateway->getLabel(),
                'fields' => $gateway->getConfigFields(),
                'supports_installments' => $gateway->supportsInstallments(),
                'payment_methods' => $gateway->getSupportedPaymentMethods(),
            ];
        }

        return $schemas;
    }

    /**
     * Verifica se um gateway está registrado.
     */
    public function has(string $name): bool
    {
        return isset($this->gateways[$name]);
    }
}