<?php

declare(strict_types=1);

namespace App\User\Presentation\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\User\Domain\UserRepositoryInterface;
use App\User\Presentation\Api\UserResource;

/**
 * GET /api/v1/users. Returns every user mapped to the resource DTO.
 * Pagination is off (plain array) for now; we can switch to a Doctrine-backed
 * paginator when result sets grow. Fine for the skeleton.
 *
 * @implements ProviderInterface<UserResource>
 */
final readonly class UserCollectionProvider implements ProviderInterface
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return array_map(UserResource::fromDomain(...), [...$this->users->all()]);
    }
}