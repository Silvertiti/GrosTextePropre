<?php
/*
namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'evaluations')]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Entreprise::class)]
    #[ORM\JoinColumn(name: 'entreprise_id', referencedColumnName: 'id', nullable: false)]
    private Entreprise $entreprise;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'etudiant_id', referencedColumnName: 'id', nullable: false)]
    private User $etudiant;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $note;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire;

    public function __construct(User $etudiant, Entreprise $entreprise, int $note, ?string $commentaire = null)
    {
        $this->etudiant = $etudiant;
        $this->entreprise = $entreprise;
        $this->note = $note;
        $this->commentaire = $commentaire;
    }

    public function getId(): int { return $this->id; }
    public function getEntreprise(): Entreprise { return $this->entreprise; }
    public function getEtudiant(): User { return $this->etudiant; }
    public function getNote(): int { return $this->note; }
    public function getCommentaire(): ?string { return $this->commentaire; }
}
*/