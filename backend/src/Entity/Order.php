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

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $paymentMethod;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'client_id', nullable: false)]
    private Client $client;

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

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'amount' => $this->amount,
            'installments' => $this->installments,
            'payment_method' => $this->paymentMethod,
            'client_id' => $this->client->getClientId(),
            'client_name' => $this->client->getName(),
            'client_cpf' => $this->client->getCpf(),
        ];
    }
}