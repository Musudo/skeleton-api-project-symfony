<?php

declare(strict_types=1);

namespace App\User\Presentation\Api;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\User\Domain\User;
use App\User\Presentation\Api\State\RegisterUserProcessor;
use App\User\Presentation\Api\State\UserCollectionProvider;
use App\User\Presentation\Api\State\UserItemProvider;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The HTTP representation of a user — a plain DTO, NOT the Doctrine entity.
 * Every operation names its own provider/processor, so API Platform never
 * touches Doctrine directly for this resource; persistence stays behind the port.
 *
 * routePrefix: '/v1' yields /api/v1/users. Versioning is per-resource by design:
 * a future UserResourceV2 simply carries routePrefix '/v2' and the two coexist.
 */
#[ApiResource(
    shortName: 'User',
    routePrefix: '/v1',
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    operations: [
        new Get(uriTemplate: '/me', uriVariables: [], provider: \App\User\Presentation\Api\State\MeProvider::class),
        new Get(provider: UserItemProvider::class),
        new GetCollection(provider: UserCollectionProvider::class),
        new Post(processor: RegisterUserProcessor::class, status: 201),
    ],
)]
final class UserResource
{
    #[ApiProperty(identifier: true)]
    #[Groups(['user:read'])]
    public ?Uuid $id = null;

    // 'user:write' makes email the ONLY client-settable field. This is the API's
    // first validation line; the Email value object is the domain's deeper guarantee.
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['user:read', 'user:write'])]
    public string $email = '';

    #[Assert\NotBlank(groups: ['user:write'])]
    #[Assert\Length(min: 8)]
    #[Groups(['user:write'])]   // accepted on POST, NEVER serialized back out
    public ?string $password = null;

    /** @var list<string> */
    #[Groups(['user:read'])]
    public array $roles = [];

    #[Groups(['user:read'])]
    public ?\DateTimeImmutable $createdAt = null;

    public static function fromDomain(User $user): self
    {
        $r = new self();
        $r->id = $user->id();
        $r->email = $user->email();
        $r->roles = $user->getRoles();
        $r->createdAt = $user->createdAt();

        return $r;
    }
}