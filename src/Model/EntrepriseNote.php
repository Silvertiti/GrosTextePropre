<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;
use App\Model\Entreprise;
use App\Model\User;

#[ORM\Entity]
#[ORM\Table(name: 'entreprise_note')]
class EntrepriseNote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Entreprise::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Entreprise $entreprise;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'integer')]
    private int $note;

    public function __construct(Entreprise $entreprise, User $user, int $note)
    {
        $this->entreprise = $entreprise;
        $this->user = $user;
        $this->note = $note;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEntreprise(): Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(Entreprise $entreprise): void
    {
        $this->entreprise = $entreprise;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getNote(): int
    {
        return $this->note;
    }

    public function setNote(int $note): void
    {
        $this->note = $note;
    }
}
