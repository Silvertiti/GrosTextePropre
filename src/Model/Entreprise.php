<?php   
namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "entreprises")]
class Entreprise  // Retirer "final"
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string")]
    private string $nom;

    #[ORM\Column(type: "string")]
    private string $email;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $numeroTelephone = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $lienSiteWeb = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $noteEvaluation = null;

    #[ORM\Column(type: "datetimetz_immutable", nullable: false)]
    private \DateTimeImmutable $registeredAt;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $description;

    public function __construct(string $SIRET, string $email, string $numeroTelephone, string $nom, string $noteEvaluation, string $lienSiteWeb, ?string $description)
    {
        $this->SIRET = $SIRET;
        $this->email = $email;
        $this->numeroTelephone = $numeroTelephone;
        $this->nom = $nom;
        $this->noteEvaluation = $noteEvaluation;
        $this->lienSiteWeb = $lienSiteWeb;
        $this->description = $description;
        $this->registeredAt = new \DateTimeImmutable('now');
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
}
