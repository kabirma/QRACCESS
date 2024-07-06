<?php

namespace App\Entity;

use App\Repository\ImportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImportRepository::class)]
class Import
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $file = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'imports')]
    private ?User $user;

    #[ORM\ManyToOne(inversedBy: 'imports')]
    private ?Group $groups;

    #[ORM\OneToMany(targetEntity: ImportRow::class, mappedBy: 'import', cascade: ['remove'])]
    private Collection $importRows;

    #[ORM\OneToMany(targetEntity: ImportHeader::class, mappedBy: 'import', cascade: ['remove'])]
    private Collection $importHeaders;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->importHeaders = new ArrayCollection();
        $this->importRows = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): static
    {
        $this->file = $file;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getGroups(): ?Group
    {
        return $this->groups;
    }

    public function setGroups(?Group $groups): static
    {
        $this->groups = $groups;

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
            $importRow->setImport($this);
        }

        return $this;
    }

    public function removeImportRow(ImportRow $importRow): static
    {
        if ($this->importRows->removeElement($importRow)) {
            // set the owning side to null (unless already changed)
            if ($importRow->getImport() === $this) {
                $importRow->setImport(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, ImportHeader>
     */
    public function getImportHeaders(): Collection
    {
        return $this->importHeaders;
    }

    public function addImportHeader(ImportHeader $importHeader): static
    {
        if (!$this->importHeader->contains($importHeader)) {
            $this->importHeader->add($importHeader);
            $importHeader->setImport($this);
        }

        return $this;
    }

    public function removeImportHeader(ImportHeader $importHeader): static
    {
        if ($this->importHeader->removeElement($importHeader)) {
            // set the owning side to null (unless already changed)
            if ($importHeader->getImport() === $this) {
                $importHeader->setImport(null);
            }
        }

        return $this;
    }
}
