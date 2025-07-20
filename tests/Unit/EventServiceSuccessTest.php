<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\EventDto;
use App\Repository\EventRepository;
use App\Service\EventService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class EventServiceSuccessTest extends TestCase
{
    public function testCreatePersistsWhenEverythingIsValid(): void
    {
        $dto           = new EventDto();
        $dto->title    = 'OK';
        $dto->startDate= '2025-08-01T09:00:00';
        $dto->endDate  = '2025-08-01T10:00:00';

        $repo = $this->createMock(EventRepository::class);
        $repo->method('existsOverlap')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');
        $em->expects($this->once())->method('beginTransaction');
        $em->expects($this->once())->method('commit');

        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $service   = new EventService($repo, $validator, $em);

        $result = $service->create([$dto]);

        self::assertSame(['persisted' => 1, 'errors' => []], $result);
    }
}
