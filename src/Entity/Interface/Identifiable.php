<?php

declare(strict_types=1);

namespace App\Entity\Interface;

interface Identifiable
{
    public function getId(): int;

    public function hasId(): bool;
}
