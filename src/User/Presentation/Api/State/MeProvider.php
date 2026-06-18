<?php

declare(strict_types=1);

namespace App\User\Presentation\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\User\Domain\User;
use App\User\Presentation\Api\UserResource;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * GET /api/v1/me — resolves the authenticated user from the security token,
 * no path identifier (uriVariables: []).
 *
 * @implements ProviderInterface<UserResource>
 */
final readonly class MeProvider implements ProviderInterface
{
    public function __construct(private Security $security)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?UserResource
    {
        $user = $this->security->getUser();

        return $user instanceof User ? UserResource::fromDomain($user) : null;
    }
}
