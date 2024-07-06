<?php

namespace App\Entity;

use App\Repository\ImportHeaderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImportHeaderRepository::class)]
class ImportHeader
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne]
    private ?Import $import;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $field = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\OneToMany(targetEntity: ImportRow::class, mappedBy: 'header')]
    private Collection $importRows;

    #[ORM\Column(length: 255)]
    private ?string $sort = null;

    #[ORM\Column]
    private ?bool $is_exportable = null;

    #[ORM\Column]
    private ?bool $is_displayable = null;

    #[ORM\Column]
    private ?bool $contains_qr = null;


    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->importRows = new ArrayCollection();
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

    public function getField(): ?string
    {
        return $this->field;
    }

    public function getFieldArray()
    {
        return unserialize($this->field);
    }

    public function setField(string $field): static
    {
        $this->field = $field;

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

    /**
     * @return Collection<int, ImportRow>
     */
    public function getImportRows(): Collection
    {
        return $this->importRows;
    }

    public function addImportRow(ImportRow $importRow): static
    {
        if (!$this->importRows->contains($importRow)) {
            $this->importRows->add($importRow);
            $importRow->setHeader($this);
        }

        return $this;
    }

    public function removeImportRow(ImportRow $importRow): static
    {
        if ($this->importRows->removeElement($importRow)) {
            // set the owning side to null (unless already changed)
            if ($importRow->getHeader() === $this) {
                $importRow->setHeader(null);
            }
        }

        return $this;
    }

    public function getSort(): ?string
    {
        return $this->sort;
    }

    public function setSort(string $sort): static
    {
        $this->sort = $sort;

        return $this;
    }

    public function isIsExportable(): ?bool
    {
        return $this->is_exportable;
    }

    public function setIsExportable(bool $is_exportable): static
    {
        $this->is_exportable = $is_exportable;

        return $this;
    }

    public function isIsDisplayable(): ?bool
    {
        return $this->is_displayable;
    }

    public function setIsDisplayable(bool $is_displayable): static
    {
        $this->is_displayable = $is_displayable;

        return $this;
    }

    public function isContainsQr(): ?bool
    {
        return $this->contains_qr;
    }

    public function setContainsQr(bool $contains_qr): static
    {
        $this->contains_qr = $contains_qr;

        return $this;
    }
}
