<?php

declare(strict_types=1);

namespace App\User\Presentation\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\User\Domain\UserRepositoryInterface;
use App\User\Presentation\Api\UserResource;

/**
 * GET /api/v1/users. Returns every user mapped to the resource DTO.
 * Pagination is off (plain array) for now; switch to a Doctrine paginator when needed.
 *
 * @implements ProviderInterface<UserResource>
 */
final readonly class UserCollectionProvider implements ProviderInterface
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    /**
     * @return array<UserResource>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return array_map(UserResource::fromDomain(...), [...$this->users->all()]);
    }
}
