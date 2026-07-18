<?php

namespace App\Service;

class BillingDistributor
{
    /**
     * @return array<int, int>
     */
    public function distribute(int $totalAmount, int $entryAmount, int $installmentsCount): array
    {
        if ($installmentsCount < 1) {
            return [];
        }

        $amountToParcel = $totalAmount - $entryAmount;
        $baseAmount = intdiv($amountToParcel, $installmentsCount);
        $remainder = $amountToParcel % $installmentsCount;

        $installments = [];
        for ($i = 1; $i <= $installmentsCount; $i++) {
            $installments[$i] = $baseAmount;
        }

        if ($installmentsCount > 0) {
            $installments[$installmentsCount] += $remainder;
        }

        return $installments;
    }
}
