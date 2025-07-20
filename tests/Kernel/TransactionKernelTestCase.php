<?php

declare(strict_types=1);

namespace App\Tests\Kernel;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class TransactionKernelTestCase extends KernelTestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->connection = $em->getConnection();
        $this->connection->setNestTransactionsWithSavepoints(true);

        static $schemaDone = false;

        if (!$schemaDone) {
            $tool = new SchemaTool($em);
            $tool->updateSchema($em->getMetadataFactory()->getAllMetadata());
            $schemaDone = true;
        }

        $this->connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->connection->rollBack();
        parent::tearDown();
    }
}
