<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Persistence\Doctrine;

use App\User\Domain\User;
use App\User\Domain\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * The only class that knows Doctrine exists. Implements the domain port.
 * save() flushes immediately to keep the example simple; a unit-of-work setup
 * would flush once per request instead.
 */
final readonly class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function save(User $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function ofId(Uuid $id): ?User
    {
        return $this->em->find(User::class, $id);
    }

    public function ofEmail(Email $email): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['email' => (string) $email]);
    }

    public function all(): iterable
    {
        return $this->em->getRepository(User::class)->findAll();
    }
}