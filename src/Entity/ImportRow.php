<?php

namespace App\Entity;

use App\Repository\ImportRowRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImportRowRepository::class)]
class ImportRow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'importRows')]
    private ?Import $import = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $data = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'importRows')]
    private ?ImportHeader $header = null;

    #[ORM\Column]
    private ?bool $contain_qr = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $qr_code = null;

    public function __construct()
    {
        $this->importRows = new ArrayCollection();
        $this->setCreatedAt(new \DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImport(): ?Import
    {
        return $this->import;
    }

    public function setImport(?Import $import): static
    {
        $this->import = $import;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function getDataArray()
    {
        return unserialize($this->data);
    }


    public function setData(string $data): static
    {
        $this->data = $data;

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

    public function getHeader(): ?ImportHeader
    {
        return $this->header;
    }

    public function setHeader(?ImportHeader $header): static
    {
        $this->header = $header;

        return $this;
    }

    public function isContainQr(): ?bool
    {
        return $this->contain_qr;
    }

    public function setContainQr(bool $contain_qr): static
    {
        $this->contain_qr = $contain_qr;

        return $this;
    }

    public function getQrCode(): ?string
    {
        return $this->qr_code;
    }

    public function setQrCode(?string $qr_code): static
    {
        $this->qr_code = $qr_code;

        return $this;
    }

}
