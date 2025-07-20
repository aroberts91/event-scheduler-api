<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Kernel\TransactionalWebTestCase;

final class EventApiTest extends TransactionalWebTestCase
{
    public function testCreateAndList(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/events',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([[
                'title'     => 'Kick‑off',
                'startDate' => '2025-08-01T10:00:00',
                'endDate'   => '2025-08-01T11:00:00'
            ]])
        );

        self::assertResponseStatusCodeSame(201);

        $client->request('GET', '/events?q=Kick');
        self::assertResponseIsSuccessful();

        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertSame(1, $json['total']);
        self::assertSame('Kick‑off', $json['events'][0]['title']);
    }

    public function testOverlapAgainstExisting(): void
    {
        $client = $this->client;

        $client->request(
            'POST',
            '/events',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([[
                'title'=>'A',
                'startDate'=>'2025-08-01T10:00:00',
                'endDate'=>'2025-08-01T11:00:00'
            ]])
        );

        self::assertResponseStatusCodeSame(201);

        $client->request('POST',
            '/events',
            [],
            [],
            ['CONTENT_TYPE'=>'application/json'],
            json_encode([[
                'title'=>'B',
                'startDate'=>'2025-08-01T10:30:00',
                'endDate'=>'2025-08-01T11:30:00'
            ]])
        );

        self::assertResponseStatusCodeSame(400);

        $errors = json_decode($client->getResponse()->getContent(), true)['errors'];
        self::assertStringContainsString('Overlaps existing event', $errors['index_0'][0]);
    }
}
