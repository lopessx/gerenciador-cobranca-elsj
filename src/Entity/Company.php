<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'company')]
class Company implements TenantInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $razaoSocial = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'integer')]
    private int $minEntryAmount = 0;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Patient::class, cascade: ['persist', 'remove'])]
    private Collection $patients;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Billing::class, cascade: ['persist', 'remove'])]
    private Collection $billings;

    public function __construct()
    {
        $this->id = bin2hex(random_bytes(8));
        $this->patients = new ArrayCollection();
        $this->billings = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;    
    }

    public function getRazaoSocial(): ?string
    {
        return $this->razaoSocial;
    }

    public function setRazaoSocial(?string $razaoSocial): self
    {
        $this->razaoSocial = $razaoSocial;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getMinEntryAmount(): int
    {
        return $this->minEntryAmount;
    }

    public function setMinEntryAmount(int $minEntryAmount): self
    {
        $this->minEntryAmount = $minEntryAmount;

        return $this;
    }

    public function getPatients(): Collection
    {
        return $this->patients;
    }

    public function addPatient(Patient $patient): self
    {
        if (!$this->patients->contains($patient)) {
            $this->patients->add($patient);
            $patient->setCompany($this);
        }

        return $this;
    }

    public function getBillings(): Collection
    {
        return $this->billings;
    }

    public function addBilling(Billing $billing): self
    {
        if (!$this->billings->contains($billing)) {
            $this->billings->add($billing);
            $billing->setCompany($this);
        }

        return $this;
    }
}
