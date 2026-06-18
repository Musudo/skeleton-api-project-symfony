<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Security;

use App\User\Domain\PasswordHasher;
use App\User\Domain\User;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

/**
 * Hashes using the SAME algorithm Symfony's security uses to VERIFY at login
 * (configured under security.password_hashers for User::class). One source of truth.
 */
final readonly class SymfonyPasswordHasher implements PasswordHasher
{
    public function __construct(private PasswordHasherFactoryInterface $factory)
    {
    }

    public function hash(string $plainPassword): string
    {
        return $this->factory->getPasswordHasher(User::class)->hash($plainPassword);
    }
}
