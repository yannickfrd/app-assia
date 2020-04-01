<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Entity\Document;
use App\Entity\SupportGroup;
use App\Form\Model\DocumentSearch;
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

    /** @var DocumentSearch */
    protected $documentSearch;


    protected function setUp()
    {
        $dataFixtures  = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/DocumentFixturesTest.yaml",
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        /** @var DocumentRepository */
        $this->repo = $this->entityManager->getRepository(Document::class);

        $this->supportGroup = $dataFixtures["supportGroup"];
        $this->user = $dataFixtures["userSuperAdmin"];
        $this->documentSearch = (new DocumentSearch())
            ->setName("Document 666")
            ->setType(1);
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(2, $this->repo->count([]));
    }

    public function testFindAllDocumentsQueryWithoutFilters()
    {
        $query = $this->repo->findAllDocumentsQuery($this->supportGroup->getId(), new DocumentSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindAllDocumentsQueryWithFilters()
    {
        $query = $this->repo->findAllDocumentsQuery($this->supportGroup->getId(), $this->documentSearch);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllDocumentsQueryWithFilterByContent()
    {
        $query = $this->repo->findAllDocumentsQuery($this->supportGroup->getId(), $this->documentSearch->setName("Description"));
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testCountAllDocumentsWithoutCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countAllDocuments());
    }

    public function testCountAllDocumentsWithCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countAllDocuments(["user" => $this->user]));
    }

    public function testSumSizeAllDocuments()
    {
        $this->assertGreaterThan(200000 * 5, $this->repo->sumSizeAllDocuments());
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->repo = null;
        $this->supportGroup = null;
        $this->user = null;
        $this->documentSearch = null;
    }
}
