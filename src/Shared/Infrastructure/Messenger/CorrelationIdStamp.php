<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Messenger;

use Symfony\Component\Messenger\Stamp\StampInterface;

/** Travels with the message through RabbitMQ, carrying the originating correlation ID. */
final readonly class CorrelationIdStamp implements StampInterface
{
    public function __construct(public string $id)
    {
    }
}
