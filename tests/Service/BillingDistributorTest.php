<?php

namespace App\Tests\Service;

use App\Service\BillingDistributor;
use PHPUnit\Framework\TestCase;

class BillingDistributorTest extends TestCase
{
    public function testDistributesTheRemainingValueAndAssignsTheRemainderToTheLastInstallment(): void
    {
        $service = new BillingDistributor();

        $result = $service->distribute(15000, 5000, 3);

        $this->assertSame([1 => 3333, 2 => 3333, 3 => 3334], $result);
    }
}
