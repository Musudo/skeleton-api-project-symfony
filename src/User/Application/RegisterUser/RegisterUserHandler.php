<?php

declare(strict_types=1);

namespace App\User\Application\RegisterUser;

use App\User\Domain\Exception\EmailAlreadyInUse;
use App\User\Domain\PasswordHasher;
use App\User\Domain\User;
use App\User\Domain\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use Symfony\Component\Uid\Uuid;

final readonly class RegisterUserHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private PasswordHasher $hasher,
    ) {
    }

    public function __invoke(RegisterUser $command): Uuid
    {
        $email = new Email($command->email);

        if (null !== $this->users->ofEmail($email)) {
            throw new EmailAlreadyInUse($email);
        }

        $user = new User($email, $this->hasher->hash($command->password));
        $this->users->save($user);

        return $user->id();
    }
}