<?php

namespace App\Model;

// src/Domain/User.php

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'users')]
final class User
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', unique: true, nullable: false)]
    private string $email;

    #[Column(type: 'string', nullable: false)]
    private string $name;
    

    #[Column(name: 'registered_at', type: 'datetimetz_immutable', nullable: false)]
    private DateTimeImmutable $registeredAt;

    public function __construct(string $email, string $name)
    {
        $this->name = $name;
        $this->email = $email;
        $this->registeredAt = new DateTimeImmutable('now');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): string
    {
        return $this->Name;
    }


    public function getRegisteredAt(): DateTimeImmutable
    {
        return $this->registeredAt;
    }
}