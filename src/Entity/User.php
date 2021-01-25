<?php

namespace App\Entity;

use App\Form\DTO\RegistrationDTO;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(name="username", type="string", length=255)
     */
    private string $username;

    /**
     * @ORM\Column(name="email", type="string", length=255)
     */
    private string $email;

    /**
     * @ORM\Column(name="password", type="string", length=255)
     */
    private string $password;

    private function __construct()
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public static function fromRegistration(RegistrationDTO $dto, callable $passwordEncoder): self
    {
        $self = new self();

        $self->password = $passwordEncoder($self, $dto->password);
        $self->username = $dto->username;
        $self->email = $dto->email;

        return $self;
    }

    public function getRoles(): array
    {
        return [];
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSalt(): void
    {
    }

    public function eraseCredentials(): void
    {
    }
}