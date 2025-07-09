<?php

declare(strict_types=1);

namespace App\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use App\Helper\DateFormatter;

trait TimestampableEntity
{
    #[ORM\Column(nullable: true)]
    #[Groups(['list'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => DateFormatter::DATETIME_FORMAT_LONG])]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    #[Groups(['list'])]
    public ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PrePersist]
    #[Ignore]
    public function setCreatedAtAutomatically(): void
    {
        if (!isset($this->createdAt)) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    #[ORM\PrePersist, ORM\PreUpdate]
    #[Ignore]
    public function setUpdatedAtAutomatically(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
