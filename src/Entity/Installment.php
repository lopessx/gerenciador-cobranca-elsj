<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'installment')]
class Installment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Billing::class, inversedBy: 'installments')]
    #[ORM\JoinColumn(nullable: false)]
    private Billing $billing;

    #[ORM\Column(type: 'integer')]
    private int $installmentNumber;

    #[ORM\Column(type: 'integer')]
    private int $amount;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $dueDate;

    public function __construct()
    {
        $this->id = bin2hex(random_bytes(8));
        $this->dueDate = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getBilling(): Billing
    {
        return $this->billing;
    }

    public function setBilling(Billing $billing): self
    {
        $this->billing = $billing;

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

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDueDate(): \DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeInterface $dueDate): self
    {
        $this->dueDate = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dueDate->format('Y-m-d 00:00:00'));

        return $this;
    }
}
