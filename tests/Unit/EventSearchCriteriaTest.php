<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Http\EventSearchCriteria;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

final class EventSearchCriteriaTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $criteria = new EventSearchCriteria([]);

        self::assertSame('start', $criteria->sort);
        self::assertSame('ASC', $criteria->direction);
        self::assertSame(1, $criteria->page);
        self::assertSame(50, $criteria->perPage);
        self::assertNull($criteria->q);
    }

    public function testParsesIsoDates(): void
    {
        $c = new EventSearchCriteria([
            'start_after' => '2025-08-01T10:00:00',
            'start_before'=> '2025-08-02T00:00:00',
        ]);

        self::assertEquals(
            new \DateTimeImmutable('2025-08-01T10:00:00'),
            $c->startAfter
        );
        self::assertEquals(
            new \DateTimeImmutable('2025-08-02T00:00:00'),
            $c->startBefore
        );
    }

    public function testInvalidDateThrows(): void
    {
        $this->expectException(BadRequestException::class);
        new EventSearchCriteria(['start_after' => 'invalid']);
    }
}
