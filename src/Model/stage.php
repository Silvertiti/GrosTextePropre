<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;
use App\Model\Ville;


#[ORM\Entity]
#[ORM\Table(name: "stages")]
class Stage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string")]
    private string $titre;

    #[ORM\Column(type: "string")]
    private string $entreprise;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\ManyToOne(targetEntity: Ville::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ville $ville = null;

    #[ORM\Column(type: 'date', nullable: false)]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: 'date', nullable: false)]
    private \DateTimeInterface $dateFin;
    
    #[ORM\Column(type: "boolean")]
    private bool $disponible = false;

    // Getters
    public function getId(): int { return $this->id; }
    public function getTitre(): string { return $this->titre; }
    public function getEntreprise(): string { return $this->entreprise; }
    public function getDescription(): string { return $this->description; }
    public function isDisponible(): bool { return $this->disponible; }
    public function getVille(): ?Ville { return $this->ville; }

    // Setters
    public function setTitre(string $titre): void { $this->titre = $titre; }
    public function setEntreprise(string $entreprise): void { $this->entreprise = $entreprise; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setDisponible(bool $disponible): void { $this->disponible = $disponible; }
    public function setVille(?Ville $ville): void { $this->ville = $ville; }
}
