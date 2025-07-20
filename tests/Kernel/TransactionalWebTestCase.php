<?php
namespace App\Tests\Kernel;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class TransactionalWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    private static array $primedConnections = [];

    protected function setUp(): void
    {
        parent::setUp();

        static::ensureKernelShutdown();
        $this->client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em      = static::getContainer()->get(EntityManagerInterface::class);
        $connId  = spl_object_id($em->getConnection());

        if (!isset(self::$primedConnections[$connId])) {
            (new SchemaTool($em))
                ->updateSchema($em->getMetadataFactory()->getAllMetadata());
            self::$primedConnections[$connId] = true;
        }
    }

    protected function tearDown(): void
    {
        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        (new ORMPurger($em))->purge();

        parent::tearDown();
        static::ensureKernelShutdown();
    }
}
