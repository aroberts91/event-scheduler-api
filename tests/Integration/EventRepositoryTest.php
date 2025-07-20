<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\Event;
use App\Http\EventSearchCriteria;
use App\Repository\EventRepository;
use App\Tests\Kernel\TransactionKernelTestCase;

final class EventRepositoryTest extends TransactionKernelTestCase
{
    private EventRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getContainer()->get(EventRepository::class);
    }

    public function testExistsOverlap(): void
    {
        $event = new Event();
        $event->title = 'Existing';
        $event->startDate = new \DateTimeImmutable('2025-08-01T10:00:00');
        $event->endDate   = new \DateTimeImmutable('2025-08-01T11:00:00');
        $this->repository->persist($event);

        $overlap = $this->repository->existsOverlap(
            new \DateTimeImmutable('2025-08-01T10:30:00'),
            new \DateTimeImmutable('2025-08-01T11:30:00')
        );

        self::assertSame($event->getId(), $overlap);
    }

    public function testFindByCriteriaPagingAndSort(): void
    {
        for ($h = 8; $h <= 10; $h++) {
            $event            = new Event();
            $event->title     = "T{$h}";
            $event->startDate = new \DateTimeImmutable("2025-08-01T{$h}:00:00");
            $event->endDate   = new \DateTimeImmutable("2025-08-01T{$h}:30:00");
            $this->repository->persist($event);
        }

        [$rows, $total] = $this->repository->findByCriteria(
            new EventSearchCriteria([
                'start_after' => '2025-08-01T00:00:00',
                'sort'        => 'title',
                'direction'   => 'desc',
                'page'        => 1,
                'per_page'    => 2,
            ])
        );

        self::assertSame(3, $total);
        self::assertCount(2, $rows);
        self::assertSame('T9', $rows[0]->title);
    }
}
