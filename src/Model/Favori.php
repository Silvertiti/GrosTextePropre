<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;
use App\Model\User;
use App\Model\Stage;

#[ORM\Entity]
#[ORM\Table(name: 'favoris')]
class Favori
{

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'favoris')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Stage::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Stage $stage;

    public function __construct(User $user, Stage $stage)
    {
        $this->user = $user;
        $this->stage = $stage;
    }

    public function getId(): int { return $this->id; }
    public function getUser(): User { return $this->user; }
    public function getStage(): Stage { return $this->stage; }

    public function setUser(User $user): void { $this->user = $user; }
    public function setStage(Stage $stage): void { $this->stage = $stage; }
}
