<?php

namespace App\Payment;

use App\Payment\Contract\PaymentGatewayInterface;
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
}