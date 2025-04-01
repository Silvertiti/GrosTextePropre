<?php

namespace App\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'entreprises')]
final class Entreprise
{
    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $SIRET;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $numeroTelephone;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $nom;

    #[ORM\Column(type: 'integer')]
    private string $noteEvaluation;

    #[ORM\Column(type: 'integer')]
    private string $lienSiteWeb;

    #[ORM\Column(type: 'string')]
    private ?string $description = null;


    #[ORM\Column(name: 'registered_at', type: 'datetimetz_immutable', nullable: false)]

    private DateTimeImmutable $registeredAt;

    #[ORM\Column(type: 'integer')]
    private int $vues = 0;


    public function __construct(string $SIRET, string $email, string $numeroTelephone, string $nom, string $noteEvaluation, string $lienSiteWeb, string $description)
    {
        $this->SIRET = $SIRET;
        $this->email = $email;
        $this->numeroTelephone = $numeroTelephone;
        $this->nom = $nom;
        $this->noteEvaluation = $noteEvaluation;
        $this->registeredAt = new DateTimeImmutable('now');
        $this->lienSiteWeb = $lienSiteWeb;
        $this->description = $description;
    }
    

    public function getId(): int { return $this->id; }
    public function getSIRET(): string { return $this->SIRET; }
    public function getEmail(): string { return $this->email; }
    public function getNumeroTelephone(): string { return $this->numeroTelephone; }
    public function getRegisteredAt(): DateTimeImmutable { return $this->registeredAt; }
    public function getNom(): string { return $this->nom; }
    public function getNoteEvaluation(): string { return $this->noteEvaluation; }
    public function getLienSiteWeb(): string { return $this->lienSiteWeb; }
    public function getDescription(): string { return $this->description; }
    
    public function setSIRET(string $SIRET): void { $this->SIRET = $SIRET; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setNumeroTelephone(string $numeroTelephone): void { $this->numeroTelephone = $numeroTelephone; }
    public function setNom(string $nom): void { $this->nom = $nom;}
    public function setNoteEvaluation(string $noteEvaluation): void { $this->noteEvaluation = $noteEvaluation; }
    public function setLienSiteWeb(string $lienSiteWeb): void { $this->lienSiteWeb = $lienSiteWeb; }
    public function setDescription(string $description): void { $this->description = $description; }

    public function getVues(): int
{
    return $this->vues;
}

public function setVues(int $vues): void
{
    $this->vues = $vues;
}

}
