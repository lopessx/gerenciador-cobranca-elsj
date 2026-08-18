<?php

namespace App\Payment;

use App\Entity\Order;
use App\Payment\Contract\PaymentGatewayInterface;
use App\Payment\DTO\PaymentResult;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
    ) {
    }

    abstract public function getName(): string;
    abstract public function getLabel(): string;
    abstract public function getConfigFields(): array;
    abstract public function getSupportedPaymentMethods(): array;

    /**
     * Implementação padrão de processInstallments para gateways que não suportam carnê.
     * Simplesmente processa a Order como uma única cobrança.
     *
     * Gateways que suportam parcelamento (como PagHiper carnê) devem sobrescrever este método.
     *
     * @return PaymentResult[]
     */
    public function processInstallments(Order $order, array $config): array
    {
        $result = $this->processOrder($order, $config);
        return [$result];
    }
}
