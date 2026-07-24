<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $orderId;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank]
    private string $amount;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    private int $installments;

    #[ORM\ManyToOne(targetEntity: PaymentMethod::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'payment_method_id', referencedColumnName: 'paymentmethod_id', nullable: false)]
    private PaymentMethod $paymentMethod;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'user_id', nullable: false)]
    private User $user;

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getInstallments(): int
    {
        return $this->installments;
    }

    public function setInstallments(int $installments): self
    {
        $this->installments = $installments;
        return $this;
    }

    public function getPaymentMethod(): PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(PaymentMethod $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'amount' => $this->amount,
            'installments' => $this->installments,
            'payment_method_id' => $this->paymentMethod->getPaymentmethodId(),
            'user_id' => $this->user->getUserId(),
        ];
    }
}