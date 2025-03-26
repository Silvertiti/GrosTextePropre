<?php
/*
namespace App\Model;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use InvalidArgumentException;

#[ORM\Entity]
#[ORM\Table(name: 'utilisateurs')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100, nullable: false)]
    private string $nom;

    #[ORM\Column(type: 'string', length: 100, nullable: false)]
    private string $prenom;

    #[ORM\Column(type: 'string', unique: true, length: 255, nullable: false)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $motDePasse;

    #[ORM\Column(type: 'string', length: 20, nullable: false, enumType: Role::class)]
    private Role $role;

    #[ORM\ManyToOne(targetEntity: Promotion::class)]
    #[ORM\JoinColumn(name: "promotion_id", referencedColumnName: "id", nullable: true)]
    private ?Promotion $promotion = null;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'actif'], nullable: false)]
    private string $statut = 'actif';

    #[ORM\Column(name: 'registered_at', type: 'datetimetz_immutable', nullable: false)]
    private DateTimeImmutable $registeredAt;

    public function __construct(string $nom, string $prenom, string $email, string $motDePasse, Role $role, ?Promotion $promotion = null)
    {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->motDePasse = password_hash($motDePasse, PASSWORD_BCRYPT);
        $this->role = $role;
        $this->registeredAt = new DateTimeImmutable('now');

        // Gestion des règles de promotion en fonction du rôle
        if ($role === Role::ETUDIANT && $promotion === null) {
            throw new InvalidArgumentException("Un étudiant doit avoir une promotion.");
        }

        if ($role === Role::ADMIN && $promotion !== null) {
            throw new InvalidArgumentException("Un administrateur ne peut pas avoir de promotion.");
        }

        $this->promotion = $promotion;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getRegisteredAt(): DateTimeImmutable
    {
        return $this->registeredAt;
    }
}
*/