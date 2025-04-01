<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "candidatures")]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: "App\Model\User")]
    #[ORM\JoinColumn(name: "etudiant_id", referencedColumnName: "id", nullable: false)]
    private $etudiant;

    #[ORM\ManyToOne(targetEntity: "App\Model\Stage")]
    #[ORM\JoinColumn(name: "stage_id", referencedColumnName: "id", nullable: false)]
    private $stage;

    #[ORM\Column(type: "string", length: 255)]
    private $cv;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $lettreMotivation;

    #[ORM\Column(type: "datetime")]
    private $dateCandidature;

    // Getters et setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtudiant(): ?User
    {
        return $this->etudiant;
    }

    public function setEtudiant(User $etudiant): self
    {
        $this->etudiant = $etudiant;
        return $this;
    }

    public function getStage(): ?Stage
    {
        return $this->stage;
    }

    public function setStage(Stage $stage): self
    {
        $this->stage = $stage;
        return $this;
    }

    public function getCv(): ?string
    {
        return $this->cv;
    }

    public function setCv(string $cv): self
    {
        $this->cv = $cv;
        return $this;
    }

    public function getLettreMotivation(): ?string
    {
        return $this->lettreMotivation;
    }

    public function setLettreMotivation(?string $lettreMotivation): self
    {
        $this->lettreMotivation = $lettreMotivation;
        return $this;
    }

    public function getDateCandidature(): ?\DateTimeInterface
    {
        return $this->dateCandidature;
    }

    public function setDateCandidature(\DateTimeInterface $dateCandidature): self
    {
        $this->dateCandidature = $dateCandidature;
        return $this;
    }
    public function getMotivation(): ?string
    {
        return $this->lettreMotivation;
    }

    public function setMotivation(?string $lettreMotivation): self
    {
        $this->lettreMotivation = $lettreMotivation;
        return $this;
    }
}