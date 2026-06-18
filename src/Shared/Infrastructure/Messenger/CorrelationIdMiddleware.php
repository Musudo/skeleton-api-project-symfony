<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Messenger;

use App\Shared\Infrastructure\Logging\CorrelationId;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

/**
 * On dispatch (web process): attach the current correlation ID as a stamp.
 * On consume (worker process): the ReceivedStamp is present, so restore the ID from
 * the stamp into this process's holder — and the worker's logs now share the web
 * request's ID.
 */
final readonly class CorrelationIdMiddleware implements MiddlewareInterface
{
    public function __construct(private CorrelationId $correlationId)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (null !== $envelope->last(ReceivedStamp::class)) {
            $stamp = $envelope->last(CorrelationIdStamp::class);
            if (null !== $stamp) {
                $this->correlationId->set($stamp->id);
            }
        } elseif (null === $envelope->last(CorrelationIdStamp::class)) {
            $envelope = $envelope->with(new CorrelationIdStamp($this->correlationId->get()));
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
