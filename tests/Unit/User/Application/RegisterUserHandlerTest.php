<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Application;

use App\User\Application\RegisterUser\RegisterUser;
use App\User\Application\RegisterUser\RegisterUserHandler;
use App\User\Domain\Exception\EmailAlreadyInUse;
use App\User\Domain\PasswordHasher;
use App\User\Domain\User;
use App\User\Domain\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class RegisterUserHandlerTest extends TestCase
{
    public function testRegistersHashesAndDispatches(): void
    {
        $repo = $this->createMock(UserRepositoryInterface::class);
        $repo->method('ofEmail')->willReturn(null);
        $repo->expects(self::once())->method('save');

        $hasher = $this->createMock(PasswordHasher::class);
        $hasher->expects(self::once())->method('hash')->with('secret123')->willReturn('hashed');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())->method('dispatch')->willReturn(new Envelope(new \stdClass()));

        $id = (new RegisterUserHandler($repo, $hasher, $bus))(new RegisterUser('new@example.com', 'secret123'));

        self::assertNotNull($id);
    }

    public function testRejectsDuplicateEmail(): void
    {
        $repo = $this->createMock(UserRepositoryInterface::class);
        $repo->method('ofEmail')->willReturn(new User(new Email('taken@example.com'), 'hash'));
        $repo->expects(self::never())->method('save');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = new RegisterUserHandler($repo, $this->createMock(PasswordHasher::class), $bus);

        $this->expectException(EmailAlreadyInUse::class);
        $handler(new RegisterUser('taken@example.com', 'secret123'));
    }
}