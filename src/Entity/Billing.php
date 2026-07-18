<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'billing')]
class Billing implements TenantInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'billings')]
    #[ORM\JoinColumn(nullable: false)]
    private Company $company;

    #[ORM\ManyToOne(targetEntity: Patient::class, inversedBy: 'billings')]
    #[ORM\JoinColumn(nullable: false)]
    private Patient $patient;

    #[ORM\Column(type: 'integer')]
    private int $totalAmount;

    #[ORM\Column(type: 'integer')]
    private int $entryAmount;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'pending';

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'billing', targetEntity: Installment::class, cascade: ['persist', 'remove'])]
    private Collection $installments;

    public function __construct()
    {
        $this->id = bin2hex(random_bytes(8));
        $this->createdAt = new \DateTimeImmutable();
        $this->installments = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getPatient(): Patient
    {
        return $this->patient;
    }

    public function setPatient(Patient $patient): self
    {
        $this->patient = $patient;

        return $this;
    }

    public function getTotalAmount(): int
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(int $totalAmount): self
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getEntryAmount(): int
    {
        return $this->entryAmount;
    }

    public function setEntryAmount(int $entryAmount): self
    {
        $this->entryAmount = $entryAmount;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getInstallments(): Collection
    {
        return $this->installments;
    }

    public function addInstallment(Installment $installment): self
    {
        if (!$this->installments->contains($installment)) {
            $this->installments->add($installment);
            $installment->setBilling($this);
        }

        return $this;
    }
}
