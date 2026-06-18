<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use App\Shared\Infrastructure\Logging\CorrelationId;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Reads X-Request-Id from the incoming request (or generates one), and echoes it back
 * on the response so clients/proxies can correlate too. Runs very early on request,
 * very late on response.
 */
final readonly class CorrelationIdSubscriber implements EventSubscriberInterface
{
    public function __construct(private CorrelationId $correlationId)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 256],
            KernelEvents::RESPONSE => ['onResponse', -256],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $id = $event->getRequest()->headers->get('X-Request-Id') ?? bin2hex(random_bytes(8));
        $this->correlationId->set($id);
    }

    public function onResponse(ResponseEvent $event): void
    {
        if ($event->isMainRequest()) {
            $event->getResponse()->headers->set('X-Request-Id', $this->correlationId->get());
        }
    }
}
