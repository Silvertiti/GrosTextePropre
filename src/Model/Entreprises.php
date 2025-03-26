<?php
/*
namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'entreprises')]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $nom;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $email;

    #[ORM\Column(type: 'string', length: 20, nullable: false)]
    private string $telephone;

    #[ORM\Column(type: 'float', options: ['default' => 0])]
    private float $moyenneEvaluation = 0;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'pilote_id', referencedColumnName: 'id', nullable: true)]
    private ?User $pilote = null;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'actif'], nullable: false)]
    private string $statut = 'actif';

    public function __construct(string $nom, string $email, string $telephone, ?string $description = null, ?User $pilote = null)
    {
        $this->nom = $nom;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->description = $description;
        $this->pilote = $pilote;
    }

    public function getId(): int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getEmail(): string { return $this->email; }
    public function getTelephone(): string { return $this->telephone; }
    public function getDescription(): ?string { return $this->description; }
    public function getPilote(): ?User { return $this->pilote; }
    public function getStatut(): string { return $this->statut; }
}
*/