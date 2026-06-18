<?php

declare(strict_types=1);

namespace App\User\Domain;

use App\Shared\Domain\AbstractEntity;
use App\User\Domain\ValueObject\Email;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User extends AbstractEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $roles;

    #[ORM\Column(type: 'string')]
    private string $passwordHash;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    /** @param list<string> $roles */
    public function __construct(Email $email, string $passwordHash, array $roles = ['ROLE_USER'], ?Uuid $id = null)
    {
        parent::__construct($id);
        $this->email = (string) $email;
        $this->passwordHash = $passwordHash;
        $this->roles = $roles;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function email(): string
    {
        return $this->email;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    // --- Symfony Security contracts ---

    public function getUserIdentifier(): string
    {
        // The Email value object guarantees a non-empty, validated address, but that
        // guarantee is lost once Doctrine hydrates it into a plain string property.
        // The assert re-narrows it to satisfy UserInterface's non-empty-string contract.
        \assert('' !== $this->email);

        return $this->email;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    public function getPassword(): string
    {
        return $this->passwordHash;
    }

    // Note: Symfony 8 removed eraseCredentials() from UserInterface — we store no
    // plaintext, so there's nothing to erase.
}
