<?php

declare(strict_types=1);

namespace App\User\Application\Notification;

/** Async side-effect message. Carries only the id — small and serializable for the queue. */
final readonly class NotifyUserRegistered
{
    public function __construct(public string $userId)
    {
    }
}
