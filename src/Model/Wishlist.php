<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'wish_list')]
class WishList
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'etudiant_id', referencedColumnName: 'id', nullable: false)]
    private User $etudiant;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Offre::class)]
    #[ORM\JoinColumn(name: 'offre_id', referencedColumnName: 'id', nullable: false)]
    private Offre $offre;

    public function __construct(User $etudiant, Offre $offre)
    {
        $this->etudiant = $etudiant;
        $this->offre = $offre;
    }
}
