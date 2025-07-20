<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\EventDto;
use App\Repository\EventRepository;
use App\Service\EventService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class EventServiceOverlapTest extends TestCase
{
    private EventService $service;

    protected function setUp(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $repo = $this->createMock(EventRepository::class);
        $repo->method('existsOverlap')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);

        $this->service = new EventService($repo, $validator, $em);
    }

    public function testDetectsOverlapInsideBatch(): void
    {
        $dto1 = new EventDto();
        $dto1->title = 'Event 1';
        $dto1->startDate = '2025-08-01T09:00:00';
        $dto1->endDate = '2025-08-01T10:00:00';

        $dto2 = new EventDto();
        $dto2->title = 'B';
        $dto2->startDate = '2025-08-01T09:30:00';
        $dto2->endDate = '2025-08-01T11:00:00';

        $result = $this->service->create([$dto1, $dto2]);

        self::assertSame(0, $result['persisted']);
        self::assertSame(['index_1' => ['Overlaps event index 0 in this request.']], $result['errors']);
    }
}
