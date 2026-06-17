<?php

declare(strict_types=1);

namespace App\User\Application\RegisterUser;

use App\User\Domain\Exception\EmailAlreadyInUse;
use App\User\Domain\User;
use App\User\Domain\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use Symfony\Component\Uid\Uuid;

/**
 * The RegisterUser use case. Depends only on a domain port, never on Doctrine or
 * HTTP, so it runs from any inbound adapter and unit-tests without booting a kernel.
 * Invoked directly for now; in Step 7 we route it through the Messenger command bus.
 */
final readonly class RegisterUserHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {
    }

    public function __invoke(RegisterUser $command): Uuid
    {
        $email = new Email($command->email);

        if (null !== $this->users->ofEmail($email)) {
            throw new EmailAlreadyInUse($email);
        }

        $user = new User($email);
        $this->users->save($user);

        return $user->id();
    }
}