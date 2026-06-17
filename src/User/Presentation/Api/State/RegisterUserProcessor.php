<?php

declare(strict_types=1);

namespace App\User\Presentation\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\User\Application\RegisterUser\RegisterUser;
use App\User\Application\RegisterUser\RegisterUserHandler;
use App\User\Domain\UserRepositoryInterface;
use App\User\Presentation\Api\UserResource;

/**
 * POST /api/v1/users. Receives the validated UserResource, delegates to the SAME
 * use case the CLI command uses, then maps the resulting domain User back out.
 * Any EmailAlreadyInUse thrown inside bubbles up and becomes a 409 problem+json
 * via exception_to_status — no try/catch needed here.
 *
 * @implements ProcessorInterface<UserResource, UserResource>
 */
final readonly class RegisterUserProcessor implements ProcessorInterface
{
    public function __construct(
        private RegisterUserHandler $handler,
        private UserRepositoryInterface $users,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserResource
    {
        $id = ($this->handler)(new RegisterUser($data->email));

        return UserResource::fromDomain($this->users->ofId($id));
    }
}