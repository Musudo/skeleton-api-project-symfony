<?php

declare(strict_types=1);

namespace App\Shared\Domain;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Base class for all entities and aggregate roots.
 *
 * Identity strategy: UUIDv7, generated in PHP at construction (NOT by the DB).
 * UUIDv7 is time-ordered, so rows insert in roughly sequential order — index
 * locality stays close to an auto-increment key while the id remains globally
 * unique and safe to mint client-side. That matters for two of this skeleton's
 * target cases: offline-first mobile clients that create ids before syncing,
 * and distributed services that can't share a DB sequence.
 *
 * #[MappedSuperclass]: contributes the id column to its children but is not an
 * entity itself, so it creates no table of its own.
 */
#[ORM\MappedSuperclass]
abstract class AbstractEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    protected Uuid $id;

    public function __construct(?Uuid $id = null)
    {
        // Allow injecting an id (e.g. reconstituting in tests or from a client),
        // otherwise mint a fresh time-ordered UUIDv7.
        $this->id = $id ?? Uuid::v7();
    }

    public function id(): Uuid
    {
        return $this->id;
    }
}