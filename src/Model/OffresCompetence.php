<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'offre_competences')]
class OffreCompetence
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Offre::class, inversedBy: 'competences')]
    #[ORM\JoinColumn(name: 'offre_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Offre $offre;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Competence::class)]
    #[ORM\JoinColumn(name: 'competence_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Competence $competence;

    public function __construct(Offre $offre, Competence $competence)
    {
        $this->offre = $offre;
        $this->competence = $competence;
    }

    public function getOffre(): Offre { return $this->offre; }
    public function getCompetence(): Competence { return $this->competence; }
}
