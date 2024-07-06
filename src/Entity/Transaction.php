<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Product $product = null;

    #[ORM\Column(length: 255)]
    private ?string $chargeId = null;

    #[ORM\Column(length: 255)]
    private ?string $price = null;

    #[ORM\Column(length: 255)]
    private ?string $credit = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    public function __construct() {
        $this->setCreatedAt(new \DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getChargeId(): ?string
    {
        return $this->chargeId;
    }

    public function setChargeId(string $chargeId): static
    {
        $this->chargeId = $chargeId;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCredit(): ?string
    {
        return $this->credit;
    }

    public function setCredit(string $credit): static
    {
        $this->credit = $credit;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
