<?php

namespace App\Tests\Repository;

use App\Entity\Support\Document;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\DocumentSearch;
use App\Form\Model\Support\SupportDocumentSearch;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class DocumentRepositoryTest extends WebTestCase
{
    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var DocumentRepository */
    protected $documentRepo;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var User */
    protected $user;

    /** @var SupportDocumentSearch */
    protected $search;

    protected function setUp(): void
    {        
        /** @var AbstractDatabaseTool */
        $databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/DocumentFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* @var DocumentRepository */
        $this->documentRepo = $this->entityManager->getRepository(Document::class);

        $this->supportGroup = $fixtures['supportGroup1'];
        $this->user = $fixtures['userRoleUser'];
        $this->search = (new SupportDocumentSearch())
            ->setName('Document')
            ->setType(1);
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(2, $this->documentRepo->count([]));
    }

    public function testFindDocumentsQueryWithoutFilters()
    {
        $qb = $this->documentRepo->findDocumentsQuery(new DocumentSearch());
        $this->assertGreaterThanOrEqual(5, count($qb->getResult()));
    }

    public function testFindSupportDocumentsQueryWithoutFilters()
    {
        $qb = $this->documentRepo->findSupportDocumentsQuery($this->supportGroup, new SupportDocumentSearch());
        $this->assertGreaterThanOrEqual(5, count($qb->getResult()));
    }

    public function testFindSupportDocumentsQueryWithFilters()
    {
        $qb = $this->documentRepo->findSupportDocumentsQuery($this->supportGroup, $this->search);
        $this->assertGreaterThanOrEqual(1, count($qb->getResult()));
    }

    public function testFindSupportsDocumentsQueryWithFilterByContent()
    {
        $qb = $this->documentRepo->findSupportDocumentsQuery($this->supportGroup, $this->search->setName('Description'));
        $this->assertGreaterThanOrEqual(1, count($qb->getResult()));
    }

    public function testCountDocumentsWithoutCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->documentRepo->countDocuments());
    }

    public function testCountDocumentsWithCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->documentRepo->countDocuments(['user' => $this->user]));
    }

    public function testSumSizeAllDocuments()
    {
        $this->assertGreaterThan(200000 * 5, $this->documentRepo->sumSizeAllDocuments());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->documentRepo = null;
        $this->supportGroup = null;
        $this->user = null;
        $this->search = null;
    }
}
