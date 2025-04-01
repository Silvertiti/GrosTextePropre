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

    private ?string $cvPath = null;  

    public function __construct(Stage $stage, User $user, string $motivation)
    {
        $this->stage = $stage;
        $this->user = $user;
        $this->motivation = $motivation;
    }


    public function setCvPath(string $cvPath): void {$this->cvPath = $cvPath;}
    
    public function getCvPath(): ?string { return $this->cvPath;}
    public function getId(): int { return $this->id; }
    public function getStage(): Stage { return $this->stage; }
    public function getUser(): User { return $this->user; }
    public function getMotivation(): string { return $this->motivation; }
}
