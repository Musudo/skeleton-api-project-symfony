<?php

declare(strict_types=1);

namespace App\User\Domain;

use App\Shared\Domain\AbstractEntity;
use App\User\Domain\ValueObject\Email;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
// "user" is a reserved word in PostgreSQL — name the table explicitly.
#[ORM\Table(name: 'users')]
class User extends AbstractEntity
{
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $roles;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    /**
     * Takes an Email value object, so a User can never be built from an invalid
     * address; it is persisted as a plain column. Credentials/auth arrive in Step 6.
     *
     * @param list<string> $roles
     */
    public function __construct(Email $email, array $roles = ['ROLE_USER'], ?Uuid $id = null)
    {
        parent::__construct($id);            // mints a UUIDv7 if none supplied
        $this->email = (string) $email;
        $this->roles = $roles;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function email(): string
    {
        return $this->email;
    }

    /** @return list<string> */
    public function roles(): array
    {
        return $this->roles;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}