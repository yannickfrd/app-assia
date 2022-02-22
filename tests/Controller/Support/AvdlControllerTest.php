<?php

namespace App\Tests\Controller\Support;

use App\Tests\AppTestTrait;
use App\Entity\Support\SupportGroup;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class AvdlControllerTest extends WebTestCase
{
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;
    
    /** @var array */
    protected $fixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/AvdlSupportFixturesTest.yaml',
        ]);

        $this->supportGroup = $this->fixtures['supportGrpAvdl1'];
    }

    public function testSearchAvdlSupportsIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/avdl-supports');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Suivis AVDL');

        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [
            'diagOrSupport' => 1,
            'supportType' => [1, 2],
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Suivis AVDL');
        $this->assertGreaterThanOrEqual(2, $crawler->filter('tr')->count());
    }

    public function testExportAvdlSupportsIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/avdl-supports');

        // Export without result
        $this->client->submitForm('export', [
            'supportDates' => 1,
            'date[start]' => (new \DateTime())->modify('+1 year')->format('Y-m-d'),
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'Aucun résultat à exporter.');

        // Export with results
        $this->client->submitForm('export', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('.spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreateAvdlSupportGroupIsSuccessful()
    {
        $user = $this->fixtures['userRoleUser'];
        $this->createLogin($user);

        $id = $this->fixtures['peopleGroup3']->getId();
        $this->client->request('POST', "/people-group/$id/new-support", [
            'support' => [
                'service' => $this->fixtures['serviceAvdl'],
                'device' => $this->fixtures['deviceAvdl']->getCode(),
                'referent' => $user,
            ],
        ]);

        $now = new \DateTime();
        $this->client->submitForm('send', [
            'support' => [
                'service' => $this->fixtures['serviceAvdl'],
                'device' => $this->fixtures['deviceAvdl']->getCode(),
                'status' => SupportGroup::STATUS_IN_PROGRESS,
                'referent' => $user,
                'originRequest' => [
                    'orientationDate' => $now->format('Y-m-d'),
                    'organization' => $this->fixtures['comedDalo'],
                ],
                'avdl' => [
                    'diagStartDate' => '2020-01-01',
                    'diagType' => 1,
                    // 'supportStartDate' => $now->format('Y-m-d'),
                ],
                'agreement' => true,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Le suivi social est créé.');
    }

    public function testEditAvdlSupportGroupIsSuccessful()
    {
        $user = $this->fixtures['userRoleUser'];
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->fixtures['supportGrpAvdl1']->getId();
        $this->client->request('GET', "/support/$id/edit");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Édition du suivi');

        $now = new \DateTime();
        $this->client->submitForm('send', [
            'support' => [
                'service' => $this->fixtures['serviceAvdl'],
                'device' => $this->fixtures['deviceAvdl']->getCode(),
                'status' => SupportGroup::STATUS_IN_PROGRESS,
                'referent' => $user,
                'originRequest' => [
                    'orientationDate' => $now->format('Y-m-d'),
                    'organization' => $this->fixtures['comedDalo'],
                    'comment' => 'XXX',
                ],
                'avdl' => [
                    'diagStartDate' => '2020-01-01',
                    'diagType' => 1,
                    'recommendationSupport' => 1,
                    'diagEndDate' => '2020-09-30',
                    'diagComment' => 'XXX',
                    'supportStartDate' => '2020-10-01',
                    'supportType' => 3,
                    'supportEndDate' => $now->format('Y-m-d'),
                    'propoHousingDate' => '2021-03-19',
                    'accessHousingModality' => 2,
                ],
                'agreement' => true,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Le suivi social est mis à jour.');

        $this->client->request('GET', "/support/$id/view");
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        $this->client = null;
        $this->fixtures = null;

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();

    }
}
