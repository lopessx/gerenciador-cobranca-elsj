<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_OPERATOR = 'ROLE_OPERATOR';
    public const ROLE_READER = 'ROLE_READER';

    public const ROLES_LABELS = [
        self::ROLE_ADMIN => 'Administrador',
        self::ROLE_OPERATOR => 'Operador',
        self::ROLE_READER => 'Leitor',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'json')]
    private array $roles = [self::ROLE_OPERATOR];

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'boolean')]
    private bool $mustChangePwd = true;

    #[ORM\ManyToMany(targetEntity: Company::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'company_user')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'company_id', referencedColumnName: 'id')]
    private Collection $companies;

    public function __construct()
    {
        $this->id = bin2hex(random_bytes(8));
        $this->companies = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getRoleLabel(): string
    {
        $role = $this->roles[0] ?? self::ROLE_OPERATOR;

        return self::ROLES_LABELS[$role] ?? 'Operador';
    }

    public function isAdmin(): bool
    {
        return in_array(self::ROLE_ADMIN, $this->roles, true);
    }

    public function isOperator(): bool
    {
        return in_array(self::ROLE_OPERATOR, $this->roles, true);
    }

    public function isReader(): bool
    {
        return in_array(self::ROLE_READER, $this->roles, true);
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

    public function isMustChangePwd(): bool
    {
        return $this->mustChangePwd;
    }

    public function setMustChangePwd(bool $mustChangePwd): self
    {
        $this->mustChangePwd = $mustChangePwd;

        return $this;
    }

    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
        }

        return $this;
    }

    public function removeCompany(Company $company): self
    {
        $this->companies->removeElement($company);

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
    }
}