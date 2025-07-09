<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Event;
use App\Model\EventDto;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class EventService
{
    public const int BATCH_SIZE = 500;

    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    /**
     * @param EventDto[] $dtos
     * @return array{persisted: int, errors: array<int, string[]>}
     */
    public function create(array $dtos): array
    {
        $persisted = 0;

        if ($errors = $this->validateDtos($dtos)) {
            return ['persisted' => $persisted, 'errors' => $errors];
        }

        if ($errors = $this->detectIncomingOverlaps($dtos)) {
            return ['persisted' => $persisted, 'errors' => $errors];
        }

        return $this->persist($dtos);
    }

    /** @return array<int, string[]> */
    private function validateDtos(array $eventDtos): array
    {
        $errors = [];

        foreach ($eventDtos as $index => $dto) {
            $violations = $this->validator->validate($dto);

            if ($violations->count() > 0) {
                foreach ($violations as $violation) {
                    $errors["index_$index"][] = $violation->getMessage();
                }

                continue;
            }

            $start = $dto->getStartDateTime();
            $end   = $dto->getEndDateTime();

            if ($start >= $end) {
                $errors["index_$index"][] = 'Start date must be before end date.';
            }
        }

        return $errors;
    }

    /**
     * @param EventDto[] $eventDtos
     * @return array<int,string[]> keyed by original DTO index
     */
    private function detectIncomingOverlaps(array $eventDtos): array
    {
        // Index the DTOs by their start and end times for more robust overlap detection
        $indexed = [];
        foreach ($eventDtos as $idx => $dto) {
            $indexed[] = [
                'idx'   => $idx,
                'start' => $dto->getStartDateTime(),
                'end'   => $dto->getEndDateTime(),
            ];
        }

        // Sort by start time
        usort(
            $indexed,
            static fn (array $a, array $b) => $a['start'] <=> $b['start']
        );

        $errors          = [];
        $latestEnd       = null;
        $latestEndIndex  = null;

        // Single pass to detect overlaps
        foreach ($indexed as $row) {
            if ($latestEnd !== null && $row['start'] < $latestEnd) {
                $errors["index_{$row['idx']}"][] =
                    sprintf(
                        'Overlaps event index %d in this request.',
                        $latestEndIndex
                    );
            }

            if ($latestEnd === null || $row['end'] > $latestEnd) {
                $latestEnd    = $row['end'];
                $latestEndIndex = $row['idx'];
            }
        }

        return $errors;
    }

    /** @return array{persisted:int, errors: array<int, string[]>} */
    private function persist(array $eventDtos): array
    {
        $errors         = [];
        $persistedCount = 0;

        $this->em->beginTransaction();

        try {
            foreach ($eventDtos as $index => $dto) {
                $start = $dto->getStartDateTime();
                $end   = $dto->getEndDateTime();

                if ($overlapIndex = $this->eventRepository->existsOverlap($start, $end)) {
                    $errors["index_$index"][] = sprintf('Overlaps existing event with ID %d.', $overlapIndex);
                    continue;
                }

                $event            = new Event();
                $event->title     = $dto->title;
                $event->startDate = $start;
                $event->endDate   = $end;

                $this->em->persist($event);
                ++$persistedCount;

                if ($persistedCount % self::BATCH_SIZE === 0) {
                    $this->flushAndClear();
                }
            }

            if ($errors) {
                $this->em->rollback();
                return ['persisted' => 0, 'errors' => $errors];
            }

            $this->em->flush();
            $this->em->commit();

            return ['persisted' => $persistedCount, 'errors' => []];

        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    private function flushAndClear(): void
    {
        $this->em->flush();
        $this->em->clear();
    }
}
