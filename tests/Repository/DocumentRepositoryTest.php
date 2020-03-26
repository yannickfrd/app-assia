<?php

namespace App\Tests\Repository;

use App\Entity\Pole;
use App\Entity\User;
use App\Entity\Document;
use App\Form\Model\DocumentSearch;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentRepositoryTest extends WebTestCase
{

    use FixturesTrait;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var DocumentRepository
     */
    protected $repo;

    /**
     * @var Document
     */
    protected $document;

    /**
     * @var Pole
     */
    protected $pole;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var DocumentSearch
     */
    protected $documentSearch;


    protected function setUp()
    {
        $this->loadFixtureFiles([
            dirname(__DIR__, 2) . "/fixtures/UserFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/ServiceFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/PoleFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/DocumentFixtures.yaml"
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        /** @var DocumentRepository */
        $this->repo = $this->entityManager->getRepository(Document::class);

        /** @var PoleRepository */
        $repoPole = $this->entityManager->getRepository(Pole::class);

        /** @var PoleRepository */
        $repoUser = $this->entityManager->getRepository(User::class);

        $this->document = $this->repo->findOneBy(["name" => "AVDL"]);
        $this->pole = $repoPole->findOneBy(["name" => "Habitat"]);
        $this->user = $repoUser->findOneBy(["username" => "r.madelaine"]);

        $this->documentSearch = $this->getDocumentSearch();
    }

    protected function getDocumentSearch()
    {
        return (new DocumentSearch())
            ->setName("Document 666")
            ->setType(1);
    }

    // public function testCount()
    // {
    //     $this->assertGreaterThanOrEqual(50, $this->repo->count([]));
    // }

    // public function testFindAllDocumentsQueryWithoutFilters()
    // {
    //     $query = $this->repo->findAllDocumentsQuery(new DocumentSearch());
    //     $this->assertGreaterThanOrEqual(10, count($query->getResult()));
    // }
}
