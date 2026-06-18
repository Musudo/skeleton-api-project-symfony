<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

/**
 * Base type for all domain-rule violations. In Step 5 a single exception listener
 * maps subclasses of this to RFC 7807 Problem Details (4xx), so the domain never
 * needs to know HTTP exists.
 */
abstract class DomainException extends \RuntimeException
{
}