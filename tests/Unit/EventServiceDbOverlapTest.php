<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\EventDto;
use App\Repository\EventRepository;
use App\Service\EventService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class EventServiceDbOverlapTest extends TestCase
{
    public function testDetectsOverlapAgainstDatabase(): void
    {
        $dto           = new EventDto();
        $dto->title    = 'Conflict';
        $dto->startDate= '2025-08-01T09:00:00';
        $dto->endDate  = '2025-08-01T10:00:00';

        $repo = $this->createMock(EventRepository::class);
        $repo->method('existsOverlap')->willReturn(42);

        $em = $this->createStub(EntityManagerInterface::class);

        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $service   = new EventService($repo, $validator, $em);

        $result = $service->create([$dto]);

        self::assertSame(0, $result['persisted']);
        self::assertSame(
            ['index_0' => ['Overlaps existing event with ID 42.']],
            $result['errors']
        );
    }
}
