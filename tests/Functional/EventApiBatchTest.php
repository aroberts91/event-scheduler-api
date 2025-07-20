<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Service\EventService;
use App\Tests\Kernel\TransactionalWebTestCase;

final class EventApiBatchTest extends TransactionalWebTestCase
{
    public function testBatchExactlyMatchesServiceConstant(): void
    {
        $client = $this->client;

        $batch = [];
        $start = new \DateTimeImmutable('2025-08-01T00:00:00');

        for ($i = 0; $i < EventService::BATCH_SIZE; $i++) {
            $batch[] = [
                'title'     => "E{$i}",
                'startDate' => $start->modify("+{$i} hour")->format('Y-m-d\TH:i:s'),
                'endDate'   => $start->modify("+{$i} hour +30 minutes")->format('Y-m-d\TH:i:s'),
            ];
        }

        $client->request(
            'POST',
            '/events',
            [],
            [],
            ['CONTENT_TYPE'=>'application/json'],
            json_encode($batch)
        );

        self::assertResponseStatusCodeSame(201);
        self::assertStringContainsString(
            sprintf('Created %d events.', EventService::BATCH_SIZE),
            $client->getResponse()->getContent()
        );
    }
}
