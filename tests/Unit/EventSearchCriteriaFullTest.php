<?php
declare(strict_types=1);

namespace App\Tests\Unit;

use App\Http\EventSearchCriteria;
use PHPUnit\Framework\TestCase;

final class EventSearchCriteriaFullTest extends TestCase
{
    public function testAllFiltersAndDescendingTitleSort(): void
    {
        $criteria = new EventSearchCriteria([
            'q'            => 'Kick',
            'start_after'  => '2025-08-01T00:00:00',
            'start_before' => '2025-08-31T23:59:59',
            'end_after'    => '2025-08-01T00:00:00',
            'end_before'   => '2025-08-31T23:59:59',
            'sort'         => 'title',
            'direction'    => 'desc',
            'page'         => 3,
            'per_page'     => 25,
        ]);

        self::assertSame('Kick', $criteria->q);
        self::assertSame('title', $criteria->sort);
        self::assertSame('DESC',  $criteria->direction);
        self::assertEquals(new \DateTimeImmutable('2025-08-01T00:00:00'), $criteria->startAfter);
        self::assertEquals(new \DateTimeImmutable('2025-08-31T23:59:59'), $criteria->endBefore);
        self::assertSame(3,  $criteria->page);
        self::assertSame(25, $criteria->perPage);
    }
}
