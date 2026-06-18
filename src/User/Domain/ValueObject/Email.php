<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use App\User\Domain\Exception\InvalidEmail;

/**
 * Validated, normalized, immutable. Because the only way to obtain an Email is
 * through this constructor, no layer above can ever hold an invalid address.
 */
final readonly class Email
{
    public string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (false === filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmail($value);
        }
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
