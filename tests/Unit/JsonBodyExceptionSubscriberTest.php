<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\EventSubscriber\JsonBodyExceptionSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;

final class JsonBodyExceptionSubscriberTest extends TestCase
{
    public function testGroupsErrors(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $subscriber = new JsonBodyExceptionSubscriber();

        $error = NotNormalizableValueException::createForUnexpectedDataType(
            '',
            'foo',
            ['int'],
            '[0].startDate'
        );

        $exception = new PartialDenormalizationException([], [$error]);
        $event = new ExceptionEvent($kernel, $this->createRequest(), 1, $exception);

        $subscriber->onKernelException($event);
        $response = $event->getResponse();
        $data = json_decode($response->getContent(), true);

        self::assertSame(400, $response->getStatusCode());
        self::assertArrayHasKey('errors', $data);
        self::assertSame('Field "startDate" expects int, string given.', $data['errors'][0][0]);
    }

    private function createRequest(): Request
    {
        return new Request([], [], [], [], [], [
            'HTTP_HOST' => 'localhost',
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/events',
        ]);
    }
}
