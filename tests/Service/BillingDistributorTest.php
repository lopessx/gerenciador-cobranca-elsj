<?php

namespace App\Tests\Service;

use App\Service\BillingDistributor;
use PHPUnit\Framework\TestCase;

class BillingDistributorTest extends TestCase
{
    private BillingDistributor $service;

    protected function setUp(): void
    {
        $this->service = new BillingDistributor();
    }

    public function testDistributesTheRemainingValueAndAssignsTheRemainderToTheLastInstallment(): void
    {
        $result = $this->service->distribute(15000, 5000, 3);

        $this->assertSame([1 => 3333, 2 => 3333, 3 => 3334], $result);
    }

    public function testDistributesExactlyWhenDivisible(): void
    {
        $result = $this->service->distribute(12000, 0, 3);

        $this->assertSame([1 => 4000, 2 => 4000, 3 => 4000], $result);
    }

    public function testDistributesWithEntryAmount(): void
    {
        $result = $this->service->distribute(10000, 2000, 4);

        $this->assertSame([1 => 2000, 2 => 2000, 3 => 2000, 4 => 2000], $result);
    }

    public function testDistributesSingleInstallment(): void
    {
        $result = $this->service->distribute(5000, 0, 1);

        $this->assertSame([1 => 5000], $result);
    }

    public function testDistributesWithRemainderInLastInstallment(): void
    {
        $result = $this->service->distribute(100, 0, 3);

        $this->assertSame([1 => 33, 2 => 33, 3 => 34], $result);
    }

    public function testReturnsEmptyArrayForZeroInstallments(): void
    {
        $result = $this->service->distribute(1000, 0, 0);

        $this->assertSame([], $result);
    }

    public function testTotalEqualsSumOfInstallmentsPlusEntry(): void
    {
        $totalAmount = 15783;
        $entryAmount = 2500;
        $installmentsCount = 5;

        $result = $this->service->distribute($totalAmount, $entryAmount, $installmentsCount);

        $sum = array_sum($result);
        $this->assertSame($totalAmount - $entryAmount, $sum);
    }
}