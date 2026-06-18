<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Notification;

use App\User\Application\Notification\NotifyUserRegistered;
use App\User\Domain\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class NotifyUserRegisteredHandler
{
    public function __construct(
        private UserRepositoryInterface $users,
        private HubInterface $hub,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(NotifyUserRegistered $message): void
    {
        $user = $this->users->ofId(Uuid::fromString($message->userId));
        if (null === $user) {
            return;
        }

        // Stand-in for genuinely heavy work: send email, call a 3rd party, render a PDF…
        $this->logger->info('Processing welcome notification', ['email' => $user->email()]);

        // Push a real-time event to everyone subscribed to the "users" topic.
        $this->hub->publish(new Update(
            'users',
            json_encode([
                'event' => 'user.registered',
                'id' => $message->userId,
                'email' => $user->email(),
            ], JSON_THROW_ON_ERROR),
        ));
    }
}