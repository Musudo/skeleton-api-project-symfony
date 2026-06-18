<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http;

use Doctrine\DBAL\Connection;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * /health        — liveness: is the process up? Touches nothing. Cheap; for orchestrators
 *                  to decide whether to restart the container.
 * /health/ready  — readiness: can it actually serve traffic? Probes the request-path
 *                  dependencies (DB, cache). Returns 503 if any are down, so a load
 *                  balancer stops routing here. (RabbitMQ is a worker-path dep, monitored
 *                  separately — the API can still serve reads/writes if it's down.)
 *
 * Both live outside /api/v1, so no firewall and no rate limiter apply.
 */
#[AsController]
final class HealthController
{
    #[Route('/health', name: 'health', methods: ['GET'])]
    public function live(): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/health/ready', name: 'health_ready', methods: ['GET'])]
    public function ready(Connection $db, CacheItemPoolInterface $cache): JsonResponse
    {
        $checks = [
            'database' => $this->probe(static fn () => $db->executeQuery('SELECT 1')),
            'cache' => $this->probe(static fn () => $cache->hasItem('__health__')),
        ];

        $ok = !in_array(false, $checks, true);

        return new JsonResponse(
            ['status' => $ok ? 'ok' : 'degraded', 'checks' => array_map(static fn (bool $up) => $up ? 'up' : 'down', $checks)],
            $ok ? 200 : 503,
        );
    }

    private function probe(callable $check): bool
    {
        try {
            $check();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}