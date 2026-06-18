<?php

declare(strict_types=1);

namespace App\Tests\Integration\User\Infrastructure;

use App\User\Domain\User;
use App\User\Domain\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineUserRepositoryTest extends KernelTestCase
{
    private UserRepositoryInterface $users;

    protected function setUp(): void
    {
        self::bootKernel();
        // The port resolves to the Doctrine adapter; fetched from the test container.
        $this->users = self::getContainer()->get(UserRepositoryInterface::class);
    }

    public function testSavesAndRetrievesByEmail(): void
    {
        $user = new User(new Email('integration@example.com'), 'hashed');
        $this->users->save($user);

        $found = $this->users->ofEmail(new Email('integration@example.com'));

        self::assertNotNull($found);
        self::assertTrue($user->id()->equals($found->id()));
        // dama rolls this INSERT back at test end — the next test sees a clean table.
    }
}