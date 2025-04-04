<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'candidatures')]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Stage::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Stage $stage;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: "text")]
    private string $motivation;

    #[ORM\Column(type: "datetime")]
    private \DateTime $createdAt;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $cvPath = null;

    // Nouvelle colonne pour le statut
    #[ORM\Column(type: "string", length: 20)]
    private string $status = 'En attente';  // Valeur par défaut est "En attente"

    // Constantes pour les statuts
    const STATUS_EN_ATTENTE = 'En attente';
    const STATUS_REFUSE = 'Refusé';
    const STATUS_ACCEPTE = 'Accepté';

    public function __construct(Stage $stage, User $user, string $motivation)
    {
        $this->stage = $stage;
        $this->user = $user;
        $this->motivation = $motivation;
        $this->createdAt = new \DateTime(); 
    }

    public function getId(): int { return $this->id; }

    public function getStage(): Stage { return $this->stage; }
    public function setStage(Stage $stage): void { $this->stage = $stage; }

    public function getUser(): User { return $this->user; }
    public function setUser(User $user): void { $this->user = $user; }

    public function getMotivation(): string { return $this->motivation; }
    public function setMotivation(string $motivation): void { $this->motivation = $motivation; }

    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function setCreatedAt(\DateTime $createdAt): void { $this->createdAt = $createdAt; }

    public function getCvPath(): ?string { return $this->cvPath; }
    public function setCvPath(?string $cvPath): void { $this->cvPath = $cvPath; }

    // Getter et Setter pour le statut
    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        // Vérifier que le statut est valide avant de le définir
        if (!in_array($status, [self::STATUS_EN_ATTENTE, self::STATUS_REFUSE, self::STATUS_ACCEPTE])) {
            throw new \InvalidArgumentException('Statut invalide');
        }
        $this->status = $status;
    }
}
