<?php

declare(strict_types=1);

namespace App\User\Presentation\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\User\Domain\UserRepositoryInterface;
use App\User\Presentation\Api\UserResource;
use Symfony\Component\Uid\Uuid;

/**
 * GET /api/v1/users/{id}. Returning null yields a clean 404.
 *
 * @implements ProviderInterface<UserResource>
 */
final readonly class UserItemProvider implements ProviderInterface
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?UserResource
    {
        $id = $uriVariables['id'];
        $id = $id instanceof Uuid ? $id : Uuid::fromString(\is_string($id) ? $id : '');

        $user = $this->users->ofId($id);

        return null === $user ? null : UserResource::fromDomain($user);
    }
}
