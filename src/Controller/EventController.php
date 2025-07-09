<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\EventSearchCriteria;
use App\Model\EventDto;
use App\Repository\EventRepository;
use App\Service\EventService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/events', name: 'events_')]
class EventController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly SerializerInterface $serializer,
        private readonly EventService $eventService
    )
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $criteria = new EventSearchCriteria($request->query->all());
        [$rows, $total] = $this->eventRepository->findByCriteria($criteria);

        return $this->json([
            'events'  => $rows,
            'total'   => $total,
            'page'    => $criteria->page,
            'perPage' => $criteria->perPage
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        /** @var EventDto[] $dtos */
        $dtos = $this->serializer->deserialize(
            $request->getContent(),
            EventDto::class . '[]',
            'json',
            [DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true]
        );

        $result    = $this->eventService->create($dtos);

        return $result['errors']
            ? $this->json(['errors' => $result['errors']], Response::HTTP_BAD_REQUEST)
            : $this->json(['message' => "Created {$result['persisted']} events."], Response::HTTP_CREATED);
    }
}
