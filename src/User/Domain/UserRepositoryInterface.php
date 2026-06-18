<?php

declare(strict_types=1);

namespace App\User\Domain;

use App\User\Domain\ValueObject\Email;
use Symfony\Component\Uid\Uuid;

/**
 * The persistence *port*. The domain and application layers depend only on this;
 * the Doctrine implementation lives in Infrastructure and is bound in services.yaml.
 */
interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function ofId(Uuid $id): ?User;

    public function ofEmail(Email $email): ?User;

    /** @return iterable<User> */
    public function all(): iterable;
}
