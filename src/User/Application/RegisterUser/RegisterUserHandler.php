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
        private \Symfony\Component\Messenger\MessageBusInterface $bus,
    ) {
    }

    public function __invoke(RegisterUser $command): \Symfony\Component\Uid\Uuid
    {
        $email = new Email($command->email);
        if (null !== $this->users->ofEmail($email)) {
            throw new EmailAlreadyInUse($email);
        }
        $user = new User($email, $this->hasher->hash($command->password));
        $this->users->save($user);

        // Hand the side effect to RabbitMQ — the HTTP response doesn't wait for it.
        $this->bus->dispatch(new \App\User\Application\Notification\NotifyUserRegistered((string) $user->id()));

        return $user->id();
    }
}