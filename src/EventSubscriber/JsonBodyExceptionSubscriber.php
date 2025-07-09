<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;

final class JsonBodyExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException'
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof ExceptionInterface) {
            return;
        }

        if ($exception instanceof PartialDenormalizationException) {
            $errors = $this->buildValidationErrors($exception);

            $event->setResponse(
                new JsonResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST)
            );

            return;
        }

        $event->setResponse(
            new JsonResponse(['errors' => ['body' => [$exception->getMessage()]]], Response::HTTP_BAD_REQUEST)
        );
    }

    private function buildValidationErrors(PartialDenormalizationException $exception): array
    {
        $groupedErrors = [];

        foreach ($exception->getErrors() as $error) {
            $path  = $error->getPath();
            $index = 0;
            $field = $path;

            // Extract index and field name from the path if it contains an array index e.g. "[0].fieldName"
            if (preg_match('/\[(\d+)]\.(.+)/', $path, $matches)) {
                $index = (int) $matches[1];
                $field = $matches[2];
            }

            if ($error instanceof NotNormalizableValueException) {
                $expectedTypes = array_map('strtolower', $error->getExpectedTypes());
                $expectedList  = implode(' or ', $expectedTypes);
                $givenType     = strtolower($error->getCurrentType());

                $message = sprintf(
                    'Field "%s" expects %s, %s given.',
                    $field,
                    $expectedList,
                    $givenType
                );
            } else {
                $message = $error->getMessage();
            }

            $groupedErrors[$index][] = $message;
        }

        return $groupedErrors;
    }
}
