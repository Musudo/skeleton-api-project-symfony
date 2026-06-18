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

    // Stores the ALREADY-hashed password. Hashing happens in Infrastructure via a
    // domain port, so the domain never imports Symfony's hasher.
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
        return $this->email;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';   // every user is at least ROLE_USER

        return array_values(array_unique($roles));
    }

    public function getPassword(): string
    {
        return $this->passwordHash;
    }

    // Note: Symfony 8 REMOVED eraseCredentials() from UserInterface (we store no
    // plaintext, so there's nothing to erase). If you ever see an error naming it,
    // add a public no-op eraseCredentials(): void {}.
}