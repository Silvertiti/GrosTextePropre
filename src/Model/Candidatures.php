<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'candidatures')]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'etudiant_id', referencedColumnName: 'id', nullable: false)]
    private User $etudiant;

    #[ORM\ManyToOne(targetEntity: Offre::class)]
    #[ORM\JoinColumn(name: 'offre_id', referencedColumnName: 'id', nullable: false)]
    private Offre $offre;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTimeInterface $dateCandidature;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $cv;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $lettreMotivation;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'en attente'], nullable: false)]
    private string $statut = 'en attente';

    public function __construct(User $etudiant, Offre $offre, string $cv, string $lettreMotivation)
    {
        $this->etudiant = $etudiant;
        $this->offre = $offre;
        $this->cv = $cv;
        $this->lettreMotivation = $lettreMotivation;
        $this->dateCandidature = new \DateTimeImmutable();
    }
}
