<?php

declare(strict_types=1);

namespace App\User\Presentation\Cli;

use App\User\Application\RegisterUser\RegisterUser;
use App\User\Application\RegisterUser\RegisterUserHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Translates console input into the RegisterUser command and hands it to the use case.
 * A second inbound adapter alongside the HTTP processor — same use case underneath.
 */
#[AsCommand(name: 'app:user:register', description: 'Register a new user')]
final class RegisterUserCommand extends Command
{
    public function __construct(
        private readonly RegisterUserHandler $handler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'The user email address');
        $this->addArgument('password', InputArgument::REQUIRED, 'The user password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // getArgument() returns mixed; is_string() narrows it in a way PHPStan trusts
        // (a plain (string) cast on mixed doesn't satisfy max). Console args are always
        // strings at runtime, so the '' fallback never actually fires.
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $id = ($this->handler)(new RegisterUser(
            \is_string($email) ? $email : '',
            \is_string($password) ? $password : '',
        ));

        $io->success(sprintf('User registered with id %s', $id));

        return Command::SUCCESS;
    }
}
