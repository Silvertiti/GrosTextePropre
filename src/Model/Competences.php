<?php
/*
namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'competences')]
class Competence
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100, unique: true, nullable: false)]
    private string $nom;

    public function __construct(string $nom)
    {
        $this->nom = $nom;
    }

    public function getId(): int { return $this->id; }
    public function getNom(): string { return $this->nom; }
}
*/