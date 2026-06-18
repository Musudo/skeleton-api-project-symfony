<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

/**
 * Applies the 'api' limiter to every /api request, keyed by client IP.
 * The argument name $apiLimiter is how Symfony autowires the limiter named 'api'.
 * On rejection it throws a 429, which API Platform renders as problem+json with
 * a Retry-After header — same standardized error shape as everything else.
 */
final readonly class RateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(private RateLimiterFactoryInterface $apiLimiter)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 16]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest() || !str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $limit = $this->apiLimiter->create($request->getClientIp() ?? 'anonymous')->consume();

        if (!$limit->isAccepted()) {
            $retryAfter = max(1, $limit->getRetryAfter()->getTimestamp() - time());
            throw new TooManyRequestsHttpException($retryAfter, 'API rate limit exceeded.');
        }
    }
}
