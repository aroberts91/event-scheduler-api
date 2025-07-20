<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Kernel\TransactionalWebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class EventApiValidationTest extends TransactionalWebTestCase
{
    #[DataProvider('invalidPayloadProvider')]
    public function testInvalidPayloadIsRejected(string $payload, string $expectedFragment): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/events',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            $payload
        );

        self::assertResponseStatusCodeSame(400);
        self::assertStringContainsString($expectedFragment, $client->getResponse()->getContent());
    }

    public static function invalidPayloadProvider(): iterable
    {
        yield 'missing title' => [
            json_encode([[
                'startDate'=>'2025-08-01T10:00:00',
                'endDate'  =>'2025-08-01T11:00:00'
            ]]),
            'Title is required.'
        ];

        yield 'bad ISO date' => [
            json_encode([[
                'title'=>'X',
                'startDate'=>'01-08-2025 10:00',
                'endDate'  =>'2025-08-01T11:00:00'
            ]]),
            'must be in ISO-8601 format'
        ];

        yield 'end before start' => [
            json_encode([[
                'title'=>'X',
                'startDate'=>'2025-08-01T12:00:00',
                'endDate'  =>'2025-08-01T11:00:00'
            ]]),
            'Start date must be before end date.'
        ];
    }
}
