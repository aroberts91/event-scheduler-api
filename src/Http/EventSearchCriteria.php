<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;

final readonly class EventSearchCriteria
{
    public ?string $q;
    public ?\DateTimeImmutable $startAfter;
    public ?\DateTimeImmutable $startBefore;
    public ?\DateTimeImmutable $endAfter;
    public ?\DateTimeImmutable $endBefore;
    public string $sort;
    public string $direction;
    public int $page;
    public int $perPage;

    public function __construct(array $params)
    {
        $this->q            = $params['q']                   ?? null;

        $this->startAfter   = $this->parseDate($params['start_after'] ?? null, 'start_after');
        $this->startBefore  = $this->parseDate($params['start_before'] ?? null, 'start_before');
        $this->endAfter     = $this->parseDate($params['end_after'] ?? null, 'end_after');
        $this->endBefore    = $this->parseDate($params['end_before'] ?? null, 'end_before');

        $candidate   = $params['sort'] ?? 'start';
        $this->sort  = \in_array($candidate, ['title', 'start'], true) ? $candidate : 'start';

        $this->direction = \strtolower($params['direction'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

        $this->page      = max(1, (int)($params['page'] ?? 1));
        $this->perPage   = min(500, max(1, (int)($params['per_page'] ?? 50)));
    }

    private function parseDate(?string $raw, string $param): ?\DateTimeImmutable
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($raw);
        } catch (\Throwable) {
            throw new BadRequestException(sprintf('Query parameter "%s" is not a valid ISO-8601 date-time: %s', $param, $raw));
        }
    }
}
