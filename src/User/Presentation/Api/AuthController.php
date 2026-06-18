<?php

declare(strict_types=1);

namespace App\User\Presentation\Api;

use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/** The json_login authenticator handles POSTs here; this body is never reached. */
#[AsController]
final class AuthController
{
    #[Route('/api/v1/auth', name: 'api_auth', methods: ['POST'])]
    public function __invoke(): never
    {
        throw new \LogicException('Intercepted by the json_login authenticator.');
    }
}