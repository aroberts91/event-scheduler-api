<?php

declare(strict_types=1);

namespace App\Model;

final readonly class EventRow
{
    public function __construct(
        public int                $id,
        public string             $title,
        public \DateTimeImmutable $startDate,
        public \DateTimeImmutable $endDate
    ) {}
}
