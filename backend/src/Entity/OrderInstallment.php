<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'order_installments')]
class OrderInstallment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'installmentItems')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'order_id', nullable: false)]
    private Order $order;

    #[ORM\Column(type: 'integer')]
    private int $installmentNumber;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $dueDate;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $amount;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $gatewayTransactionId = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $bankSlipUrl = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $bankSlipBarcode = null;

    #[ORM\Column(type: 'string', length: 30)]
    private string $gatewayStatus = 'PENDING';

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getInstallmentNumber(): int
    {
        return $this->installmentNumber;
    }

    public function setInstallmentNumber(int $installmentNumber): self
    {
        $this->installmentNumber = $installmentNumber;
        return $this;
    }

    public function getDueDate(): \DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeInterface $dueDate): self
    {
        $this->dueDate = $dueDate;
        return $this;
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

    public function getGatewayTransactionId(): ?string
    {
        return $this->gatewayTransactionId;
    }

    public function setGatewayTransactionId(?string $gatewayTransactionId): self
    {
        $this->gatewayTransactionId = $gatewayTransactionId;
        return $this;
    }

    public function getBankSlipUrl(): ?string
    {
        return $this->bankSlipUrl;
    }

    public function setBankSlipUrl(?string $bankSlipUrl): self
    {
        $this->bankSlipUrl = $bankSlipUrl;
        return $this;
    }

    public function getBankSlipBarcode(): ?string
    {
        return $this->bankSlipBarcode;
    }

    public function setBankSlipBarcode(?string $bankSlipBarcode): self
    {
        $this->bankSlipBarcode = $bankSlipBarcode;
        return $this;
    }

    public function getGatewayStatus(): string
    {
        return $this->gatewayStatus;
    }

    public function setGatewayStatus(string $gatewayStatus): self
    {
        $this->gatewayStatus = $gatewayStatus;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order->getOrderId(),
            'installment_number' => $this->installmentNumber,
            'due_date' => $this->dueDate->format('Y-m-d'),
            'amount' => $this->amount,
            'gateway_transaction_id' => $this->gatewayTransactionId,
            'bank_slip_url' => $this->bankSlipUrl,
            'bank_slip_barcode' => $this->bankSlipBarcode,
            'gateway_status' => $this->gatewayStatus,
        ];
    }
}
