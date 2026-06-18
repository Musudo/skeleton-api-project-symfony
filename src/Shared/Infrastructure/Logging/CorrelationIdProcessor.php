<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Logging;

use Monolog\LogRecord;

/** Stamps every single log record with the current correlation ID. */
final readonly class CorrelationIdProcessor
{
    public function __construct(private CorrelationId $correlationId)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra['correlation_id'] = $this->correlationId->get();

        return $record;
    }
}