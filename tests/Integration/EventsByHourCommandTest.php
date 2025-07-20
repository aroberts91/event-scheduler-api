<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Command\EventsByHourCommand;
use App\Entity\Event;
use App\Repository\EventRepository;
use App\Tests\Kernel\TransactionKernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class EventsByHourCommandTest extends TransactionKernelTestCase
{
    public function testOutputsGrouped(): void
    {
        $repo = self::getContainer()->get(EventRepository::class);

        $event = new Event();
        $event->title = 'Breakfast';
        $event->startDate = new \DateTimeImmutable('2025-08-01T08:30:00');
        $event->endDate = new \DateTimeImmutable('2025-08-01T09:30:00');
        $repo->persist($event);

        $cmd    = new EventsByHourCommand($repo);
        $tester = new CommandTester($cmd);

        $tester->execute(['date' => '2025-08-01']);

        $tester->assertCommandIsSuccessful();
        $text = $tester->getDisplay();

        self::assertStringContainsString('08:00', $text);
        self::assertStringContainsString('08:30 — 09:30 : Breakfast', $text);
    }
}
