<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template T of object
 *
 * @extends ServiceEntityRepository<T>
 */
abstract class AbstractRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        protected readonly EntityManagerInterface $em
    )
    {
        parent::__construct($registry, $this->getEntityClass());
    }

    /**
     * @return class-string<T>
     */
    abstract protected function getEntityClass(): string;

    /**
     * @phpstan-param T $entity
     */
    public function persist(object $entity, bool $flush = true): void
    {
        $this->em->persist($entity);

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * @phpstan-param T $entity
     */
    public function delete(object $entity, bool $flush = true): void
    {
        $this->em->remove($entity);

        if ($flush) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        $this->em->flush();
    }
}
