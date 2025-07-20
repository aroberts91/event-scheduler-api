<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Command\EventsByHourCommand;
use App\Repository\EventRepository;
use App\Tests\Kernel\TransactionKernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Command\Command;

final class EventsByHourCommandInvalidDateTest extends TransactionKernelTestCase
{
    public function testInvalidDateReturnsCommandInvalid(): void
    {
        $repo   = $this->createStub(EventRepository::class);
        $cmd    = new EventsByHourCommand($repo);
        $tester = new CommandTester($cmd);

        $tester->execute(['date' => 'not-a-date']);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertStringContainsString('Invalid date format', $tester->getDisplay());
    }
}
