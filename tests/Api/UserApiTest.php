<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

final class UserApiTest extends ApiTestCase
{
    public function testRegisterReturns201(): void
    {
        static::createClient()->request('POST', '/api/v1/users', [
            'json' => ['email' => 'api@example.com', 'password' => 'sup3rsecret'],
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertJsonContains(['email' => 'api@example.com']);
    }

    public function testDuplicateReturns409(): void
    {
        $client = static::createClient();
        $payload = ['json' => ['email' => 'dupe@example.com', 'password' => 'sup3rsecret']];
        $client->request('POST', '/api/v1/users', $payload);
        $client->request('POST', '/api/v1/users', $payload);

        self::assertResponseStatusCodeSame(409);
    }

    public function testProtectedEndpointRequiresAuth(): void
    {
        static::createClient()->request('GET', '/api/v1/users');

        self::assertResponseStatusCodeSame(401);
    }

    public function testLoginReturnsToken(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/users', [
            'json' => ['email' => 'login@example.com', 'password' => 'sup3rsecret'],
        ]);
        self::assertResponseStatusCodeSame(201);

        $response = $client->request('POST', '/api/v1/auth', [
            'json' => ['email' => 'login@example.com', 'password' => 'sup3rsecret'],
        ]);

        self::assertResponseIsSuccessful();
        self::assertArrayHasKey('token', $response->toArray());
    }
}