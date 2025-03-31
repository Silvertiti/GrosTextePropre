<?php
/*
namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'offres')]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $titre;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $description;

    #[ORM\ManyToOne(targetEntity: Entreprise::class)]
    #[ORM\JoinColumn(name: 'entreprise_id', referencedColumnName: 'id', nullable: false)]
    private Entreprise $entreprise;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $remuneration;

    #[ORM\Column(type: 'date', nullable: false)]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: 'date', nullable: false)]
    private \DateTimeInterface $dateFin;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'pilote_id', referencedColumnName: 'id', nullable: true)]
    private ?User $pilote;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'active'], nullable: false)]
    private string $statut = 'active';

    public function __construct(string $titre, string $description, Entreprise $entreprise, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, ?User $pilote = null, ?float $remuneration = null)
    {
        $this->titre = $titre;
        $this->description = $description;
        $this->entreprise = $entreprise;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->pilote = $pilote;
        $this->remuneration = $remuneration;
    }
}
*/