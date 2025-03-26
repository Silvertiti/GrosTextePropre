<?php
/*
namespace App\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'promotions')]
class Promotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100, unique: true, nullable: false)]
    private string $nom;

    /** 
     * @var Collection<int, User> 
     */
    /*
    #[ORM\OneToMany(mappedBy: 'promotion', targetEntity: User::class)]
    private Collection $etudiants;

    public function __construct(string $nom)
    {
        $this->nom = $nom;
        $this->etudiants = new ArrayCollection();
    }

    public function getId(): int { return $this->id; }
    public function getNom(): string { return $this->nom; }

    public function addEtudiant(User $etudiant): void
    {
        if (!$this->etudiants->contains($etudiant)) {
            $this->etudiants->add($etudiant);
        }
    }

    public function getEtudiants(): Collection { return $this->etudiants; }
}
*/