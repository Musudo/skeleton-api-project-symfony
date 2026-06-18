<?php

declare(strict_types=1);

namespace App\User\Domain;

/** Hashing port. The application layer depends on this, never on Symfony. */
interface PasswordHasher
{
    public function hash(string $plainPassword): string;
}
