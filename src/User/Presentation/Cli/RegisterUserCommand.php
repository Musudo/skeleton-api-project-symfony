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
 * Translates console input into the RegisterUser command and hands it to the use
 * case — nothing more. The HTTP adapter in Step 5 will do the same job for requests.
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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = ($this->handler)(new RegisterUser($input->getArgument('email')));
        $io->success(sprintf('User registered with id %s', $id));

        return Command::SUCCESS;
    }
}