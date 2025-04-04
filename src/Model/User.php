<?php

namespace App\Model;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User 
{
    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $motDePasse;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $prenom;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $nom;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $role;

    #[ORM\Column(name: 'registered_at', type: 'datetimetz_immutable', nullable: false)]
    private DateTimeImmutable $registeredAt;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Favori::class)]
    private Collection $favoris;

    #[ORM\Column(type: "string", length: 20, options: ["default" => "active"])]
    private string $status;


    public function __construct(string $email, string $prenom, string $nom, string $motDePasse, string $role = 'user')
    {
        $this->email = $email;
        $this->prenom = $prenom;
        $this->nom = $nom;
        $this->motDePasse = password_hash($motDePasse, PASSWORD_BCRYPT);
        $this->role = $role;
        $this->registeredAt = new DateTimeImmutable('now');
    }
    
    public function getFavoris(): Collection { return $this->favoris; }
    public function getId(): int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getRegisteredAt(): DateTimeImmutable { return $this->registeredAt; }
    public function getMotDePasse(): string { return $this->motDePasse; }
    public function getRole(): string { return $this->role; }
    public function getPrenom(): string { return $this->prenom; }
    public function getNom(): string { return $this->nom; }
    public function getStatus(): string{return $this->status;}

    public function setEmail(string $email): void { $this->email = $email; }
    public function setMotDePasse(string $mdp): void { $this->motDePasse = $mdp; }
    public function setRole(string $role): void { $this->role = $role; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }
    public function setNom(string $nom): void { $this->nom = $nom;}
    public function setStatus(string $status): void{$this->status = $status;}
}


