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
    
    #[Column(type: 'string', nullable: false)]
    private string $password;

    #[Column(name: 'registered_at', type: 'datetimetz_immutable', nullable: false)]
    private DateTimeImmutable $registeredAt;

    #[Column(type: 'string', nullable: false)]
    private string $role;

    public function __construct(string $email, string $name, string $password, string $role)
    {
        $this->email = $email;
        $this->name = $name;
        $this->password = $password;
        $this->registeredAt = new DateTimeImmutable('now');
        $this->role = $role;
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
        return $this->name;
    }


    public function getRegisteredAt(): DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(string $password): string
    {
        return $this->role;
    }
}