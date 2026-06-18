<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Logging;

/**
 * Per-process holder for the current correlation ID. It's a shared service, so the
 * request subscriber, the Messenger middleware, and the log processor all see the
 * same value within one PHP process. Lazily generates one if nothing set it yet.
 */
final class CorrelationId
{
    private ?string $value = null;

    public function get(): string
    {
        return $this->value ??= bin2hex(random_bytes(8));
    }

    public function set(string $value): void
    {
        $this->value = $value;
    }
}
