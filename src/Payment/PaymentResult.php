<?php

namespace App\Payment;

class PaymentResult
{
    public function __construct(
        private readonly bool $success,
        private readonly string $transactionId,
        private readonly ?string $errorMessage = null,
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }
}
