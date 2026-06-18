<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Domain\ValueObject;

use App\User\Domain\Exception\InvalidEmail;
use App\User\Domain\ValueObject\Email;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testTrimsAndLowercases(): void
    {
        self::assertSame('alice@example.com', (new Email('  Alice@Example.COM '))->value);
    }

    public function testRejectsInvalidAddress(): void
    {
        $this->expectException(InvalidEmail::class);
        new Email('not-an-email');
    }
}
