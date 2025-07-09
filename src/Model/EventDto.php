<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

final class EventDto
{
    #[Assert\NotBlank(message: 'Title is required.')]
    #[Assert\Length(max:255, maxMessage: 'Title can’t exceed {{ limit }} characters.')]
    public string $title;

    #[Assert\NotBlank(message: 'Start date is required.')]
    #[Assert\DateTime(
        format: 'Y-m-d\TH:i:s',
        message: 'Start date must be in ISO-8601 format (e.g. 2025-07-22T10:00:00).'
    )]
    public string $startDate;

    #[Assert\NotBlank(message: 'End date is required.')]
    #[Assert\DateTime(
        format: 'Y-m-d\TH:i:s',
        message: 'End date must be in ISO-8601 format (e.g. 2025-07-22T11:00:00).'
    )]
    public string $endDate;

    public function getStartDateTime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->startDate);
    }

    public function getEndDateTime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->endDate);
    }
}
