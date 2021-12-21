<?php

namespace App\Tests\Repository;

use App\Entity\Organization\Service;
use App\Entity\Organization\Tag;
use App\Form\Model\Organization\TagSearch;
use App\Repository\Organization\TagRepository;
use Doctrine\ORM\EntityManager;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TagRepositoryTest extends WebTestCase
{
    /** @var EntityManager */
    private $em;

    /** @var TagRepository */
    protected $tagRepo;

    /** @var Service */
    protected $service;

    protected function setUp(): void
    {
        /** @var AbstractDatabaseTool */
        $databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/DataFixturesTest/TagFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->tagRepo = $this->em->getRepository(Tag::class);

        $this->service = $fixtures['service1'];
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(5, $this->tagRepo->count([]));
    }

    public function testFindTagByService()
    {
        $query = $this->tagRepo->findTagByService($this->service);
        $this->assertCount(1, $query);
    }

    public function testFindTagsQuery()
    {
        $query = $this->tagRepo->findTagsQuery(new TagSearch())->getResult();
        $this->assertCount(5, $query);
    }

    public function testFindAllQueryBuilder()
    {
        $query = $this->tagRepo->findAllQueryBuilder()->getQuery()->getResult();
        $this->assertCount(5, $query);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
        $this->tagRepo = null;
        $this->service = null;
    }
}
