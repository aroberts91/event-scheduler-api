<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:events:by-hour',
    description: 'List the events of a given day by hour',
)]
final class EventsByHourCommand extends Command
{
    public function __construct(private readonly EventRepository $eventRepository)
    {
        parent::__construct();
    }

    public function __invoke(SymfonyStyle $io, #[Argument(description: 'Date in YYYY-MM-DD format', name: 'date')] string $date): int
    {
        try {
            $date = new \DateTimeImmutable($date);
        } catch (\Throwable) {
            $io->error("Invalid date format: {$date}");
            return Command::INVALID;
        }

        $startOfDay = $date->setTime(0, 0);
        $endOfDay = $date->setTime(23, 59, 59);

        $events = $this->eventRepository->findForDayOrdered($startOfDay, $endOfDay);

        if (empty($events)) {
            $io->success("No events found for {$date->format('Y-m-d')}");
            return Command::SUCCESS;
        }

        $hours = [];

        /** @var Event $event */
        foreach ($events as $event) {
            $hourKey = $event->startDate->format('H:00');
            $hours[$hourKey][] = $event;
        }

        ksort($hours);

        $io->title('Events on '.$date->format('Y-m-d'));

        foreach ($hours as $hour => $list) {
            $io->section($hour);

            foreach ($list as $e) {
                $io->text(sprintf(
                    '  %s — %s : %s',
                    $e->startDate->format('H:i'),
                    $e->endDate->format('H:i'),
                    $e->title,
                ));
            }
        }

        return Command::SUCCESS;
    }
}
