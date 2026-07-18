<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'patient')]
class Patient implements TenantInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'patients')]
    #[ORM\JoinColumn(nullable: false)]
    private Company $company;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 11)]
    private string $cpf;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'active';

    #[ORM\OneToMany(mappedBy: 'patient', targetEntity: Billing::class, cascade: ['persist', 'remove'])]
    private Collection $billings;

    public function __construct()
    {
        $this->id = bin2hex(random_bytes(8));
        $this->billings = new ArrayCollection();
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function setCpf(string $cpf): self
    {
        $this->cpf = $cpf;

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

    public function getBillings(): Collection
    {
        return $this->billings;
    }

    public function addBilling(Billing $billing): self
    {
        if (!$this->billings->contains($billing)) {
            $this->billings->add($billing);
            $billing->setPatient($this);
        }

        return $this;
    }
}
