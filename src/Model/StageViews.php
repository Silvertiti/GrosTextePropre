<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;
use App\Model\Stage;
use App\Model\User;

#[ORM\Entity]
#[ORM\Table(name: 'stage_views')]
class StageViews
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

    #[ORM\Column(type: 'datetime')]
    private \DateTime $viewedAt;

    public function __construct(Stage $stage, User $user)
    {
        $this->stage = $stage;
        $this->user = $user;
        $this->viewedAt = new \DateTime(); // Valeur par défaut
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStage(): Stage
    {
        return $this->stage;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getViewedAt(): \DateTime
    {
        return $this->viewedAt;
    }

    public function setViewedAt(\DateTime $viewedAt): void
    {
        $this->viewedAt = $viewedAt;
    }
}
