<?php

namespace App\Payment;

use App\Entity\Billing;

interface PaymentGatewayInterface
{
    public function getName(): string;

    public function process(Billing $billing): PaymentResult;
}
