<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interface\Identifiable;
use App\Repository\EventRepository;
use App\Trait\IdentifiableEntity;
use App\Trait\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'idx_start_end', columns: ['start_date', 'end_date'])]
class Event implements Identifiable
{
    use IdentifiableEntity;
    use TimestampableEntity;

    #[ORM\Column(length: 255)]
    public ?string $title = null;

    #[ORM\Column(type: 'datetime_immutable')]
    public ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: 'datetime_immutable')]
    public ?\DateTimeImmutable $endDate = null;
}
