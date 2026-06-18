<?php

declare(strict_types=1);

namespace App\User\Application\RegisterUser;

/**
 * Application command: an immutable input DTO describing the intent to register
 * a user. Carries primitives from the edge; framework- and transport-agnostic.
 */
final readonly class RegisterUser
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }
}
