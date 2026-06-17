<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;
use App\User\Domain\ValueObject\Email;

final class EmailAlreadyInUse extends DomainException
{
    public function __construct(Email $email)
    {
        parent::__construct(sprintf('Email "%s" is already in use.', $email));
    }
}