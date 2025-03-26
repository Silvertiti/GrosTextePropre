<?php

namespace App\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
final class User
{
    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $motDePasse;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $role;


    #[ORM\Column(name: 'registered_at', type: 'datetimetz_immutable', nullable: false)]
    private DateTimeImmutable $registeredAt;

    public function __construct(string $email, string $name)
    {
        $this->email = $email;
        $this->name = $name;
        $this->registeredAt = new DateTimeImmutable('now');
    }

    public function getId(): int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getName(): string { return $this->name; }
    public function getRegisteredAt(): DateTimeImmutable { return $this->registeredAt; }
    public function getMotDePasse(): string { return $this->motDePasse; }
    public function getRole(): string { return $this->role; }

    public function setEmail(string $email): void { $this->email = $email; }
    public function setMotDePasse(string $mdp): void { $this->motDePasse = $mdp; }
    public function setName(string $name): void { $this->name = $name; }
    public function setRole(string $role): void { $this->role = $role; }
}
