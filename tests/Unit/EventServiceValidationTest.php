<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\EventDto;
use App\Repository\EventRepository;
use App\Service\EventService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class EventServiceValidationTest extends TestCase
{
    private EventService $service;

    protected function setUp(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $repo = $this->createMock(EventRepository::class);
        $em   = $this->createMock(EntityManagerInterface::class);

        $this->service = new EventService($repo, $validator, $em);
    }

    public function testCreateRejectsEndBeforeStart(): void
    {
        $dto = new EventDto();
        $dto->title = 'Bad Event';
        $dto->startDate = '2025-08-01T11:00:00';
        $dto->endDate   = '2025-08-01T10:00:00';

        $result = $this->service->create([$dto]);

        self::assertSame(0, $result['persisted']);
        self::assertSame([
            'index_0' => ['Start date must be before end date.']
        ], $result['errors']);
    }
}
