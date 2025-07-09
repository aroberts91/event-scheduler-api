<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Event;
use App\Http\EventSearchCriteria;
use App\Model\EventRow;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends AbstractRepository<Event>
 */
class EventRepository extends AbstractRepository
{
    public function getEntityClass(): string
    {
        return Event::class;
    }

    public function findByCriteria(EventSearchCriteria $criteria): array
    {
        // Project results into EventRow DTO objects to avoid full entity hydration while keeping DateTimeImmutable properties for serialization
        $qb = $this->createQueryBuilder('e')
            ->select(
                'NEW ' . EventRow::class
                . '(e.id, e.title, e.startDate, e.endDate)'
            );

        if ($criteria->q) {
            $qb->andWhere('e.title LIKE :q')
                ->setParameter('q', '%' . $criteria->q . '%', Types::STRING);
        }

        if ($criteria->startAfter) {
            $qb->andWhere('e.startDate >= :startAfter')
                ->setParameter('startAfter', $criteria->startAfter, Types::DATETIME_IMMUTABLE);
        }

        if ($criteria->startBefore) {
            $qb->andWhere('e.startDate <= :startBefore')
                ->setParameter('startBefore', $criteria->startBefore, Types::DATETIME_IMMUTABLE);
        }

        if ($criteria->endAfter) {
            $qb->andWhere('e.endDate >= :endAfter')
                ->setParameter('endAfter', $criteria->endAfter, Types::DATETIME_IMMUTABLE);
        }

        if ($criteria->endBefore) {
            $qb->andWhere('e.endDate <= :endBefore')
                ->setParameter('endBefore', $criteria->endBefore, Types::DATETIME_IMMUTABLE);
        }

        $orderBy = $criteria->sort === 'title' ? 'e.title' : 'e.startDate';
        $qb->orderBy($orderBy, $criteria->direction)
            ->setFirstResult(($criteria->page - 1) * $criteria->perPage)
            ->setMaxResults($criteria->perPage);

        $paginator = new Paginator($qb, true);
        $paginator->setUseOutputWalkers(false); // Fix to allow pagination with scalar results

        $total     = $paginator->count();
        $rows  = $paginator->getQuery()->getResult();

        return [$rows, $total];
    }

    public function existsOverlap(\DateTimeImmutable $start, \DateTimeImmutable $end): ?int
    {
        return $this->createQueryBuilder('e')
            ->select('e.id')
            ->where('e.startDate <= :end')
            ->andWhere('e.endDate   >= :start')
            ->setMaxResults(1)
            ->setParameters(new ArrayCollection([
                new Parameter('start', $start, Types::DATETIME_IMMUTABLE),
                new Parameter('end', $end, Types::DATETIME_IMMUTABLE),
            ]))
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }

    public function findForDayOrdered(\DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.startDate BETWEEN :start AND :end')
            ->setParameters(new ArrayCollection([
                new Parameter('start', $start, Types::DATETIME_IMMUTABLE),
                new Parameter('end', $end, Types::DATETIME_IMMUTABLE),
            ]))
            ->orderBy('e.startDate', 'ASC')
            ->addOrderBy('e.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
