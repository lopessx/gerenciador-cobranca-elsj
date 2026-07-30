<?php

namespace App\Entity;

use App\Enum\PaymentGateway;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'payment_settings')]
class PaymentSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 30, enumType: PaymentGateway::class)]
    #[Assert\NotBlank]
    private PaymentGateway $paymentGateway;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank]
    private string $optionName;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $value = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPaymentGateway(): PaymentGateway
    {
        return $this->paymentGateway;
    }

    public function setPaymentGateway(PaymentGateway $paymentGateway): self
    {
        $this->paymentGateway = $paymentGateway;
        return $this;
    }

    public function getOptionName(): string
    {
        return $this->optionName;
    }

    public function setOptionName(string $optionName): self
    {
        $this->optionName = $optionName;
        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'payment_gateway' => $this->paymentGateway->value,
            'option_name' => $this->optionName,
            'value' => $this->value,
        ];
    }
}