<?php

namespace App\Tests\Repository;

use App\Entity\Organization\User;
use App\Entity\Support\Document;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\DocumentSearch;
use App\Form\Model\Support\SupportDocumentSearch;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentRepositoryTest extends WebTestCase
{
    use FixturesTrait;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var DocumentRepository */
    protected $repo;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var User */
    protected $user;

    /** @var SupportDocumentSearch */
    protected $search;

    protected function setUp(): void
    {
        $data = $this->loadFixtureFiles([
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
        $this->repo = $this->entityManager->getRepository(Document::class);

        $this->supportGroup = $data['supportGroup1'];
        $this->user = $data['userRoleUser'];
        $this->search = (new SupportDocumentSearch())
            ->setName('Document')
            ->setType(1);
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(2, $this->repo->count([]));
    }

    public function testFindDocumentsQueryWithoutFilters()
    {
        $query = $this->repo->findDocumentsQuery(new DocumentSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindSupportDocumentsQueryWithoutFilters()
    {
        $query = $this->repo->findSupportDocumentsQuery($this->supportGroup, new SupportDocumentSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindSupportDocumentsQueryWithFilters()
    {
        $query = $this->repo->findSupportDocumentsQuery($this->supportGroup, $this->search);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindSupportsDocumentsQueryWithFilterByContent()
    {
        $query = $this->repo->findSupportDocumentsQuery($this->supportGroup, $this->search->setName('Description'));
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testCountDocumentsWithoutCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countDocuments());
    }

    public function testCountDocumentsWithCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countDocuments(['user' => $this->user]));
    }

    public function testSumSizeAllDocuments()
    {
        $this->assertGreaterThan(200000 * 5, $this->repo->sumSizeAllDocuments());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->repo = null;
        $this->supportGroup = null;
        $this->user = null;
        $this->search = null;
    }
}
