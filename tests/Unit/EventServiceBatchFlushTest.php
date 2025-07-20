<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\EventDto;
use App\Repository\EventRepository;
use App\Service\EventService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class EventServiceBatchFlushTest extends TestCase
{
    public function testFlushIsCalledTwiceForBatchPlusOne(): void
    {
        $batch = [];
        $start = new \DateTimeImmutable('2025-08-01T00:00:00');

        for ($i = 0; $i <= EventService::BATCH_SIZE; ++$i) {
            $dto            = new EventDto();
            $dto->title     = (string) $i;
            $dto->startDate = $start->modify("+{$i} hour")->format('Y-m-d\\TH:i:s');
            $dto->endDate   = $start->modify("+{$i} hour +30 minutes")->format('Y-m-d\\TH:i:s');
            $batch[]        = $dto;
        }

        $repo = $this->createMock(EventRepository::class);
        $repo->method('existsOverlap')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('beginTransaction');
        $em->method('commit');
        $em->expects($this->exactly(2))->method('flush');
        $em->expects($this->once())->method('clear');

        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $service   = new EventService($repo, $validator, $em);

        $result = $service->create($batch);

        self::assertSame(EventService::BATCH_SIZE + 1, $result['persisted']);
        self::assertSame([], $result['errors']);
    }
}
