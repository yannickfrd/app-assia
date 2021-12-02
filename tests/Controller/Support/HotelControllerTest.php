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

class HotelControllerTest extends WebTestCase
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
            dirname(__DIR__).'/../DataFixturesTest/HotelSupportFixturesTest.yaml',
        ]);

        $this->supportGroup = $this->fixtures['supportGrpHotel1'];
    }

    public function testSearchHotelSupportsIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        // Page is up
        $this->client->request('GET', '/hotel-supports');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Suivis PASH');

        // Search is successful
        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Suivis PASH');
        $this->assertGreaterThanOrEqual(2, $crawler->filter('tr')->count());
    }

    public function testExportHotelSupportsIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/hotel-supports');

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

    public function testCreateHotelSupportGroupIsSuccessful()
    {
        $user = $this->fixtures['userRoleUser'];
        $this->createLogin($user);

        $id = $this->fixtures['peopleGroup3']->getId();
        $this->client->request('POST', "/group/$id/support/new", [
            'support' => [
                'service' => $this->fixtures['servicePash'],
                'device' => $this->fixtures['deviceHotel']->getCode(),
                'referent' => $user,
            ],
        ]);

        $now = new \DateTime();
        $this->client->submitForm('send', [
            'support' => [
                'service' => $this->fixtures['servicePash'],
                'device' => $this->fixtures['deviceHotel']->getCode(),
                'status' => SupportGroup::STATUS_IN_PROGRESS,
                'referent' => $user,
                'originRequest' => [
                    'orientationDate' => $now->format('Y-m-d'),
                    'organization' => $this->fixtures['siao95'],
                    'comment' => 'XXX',
                ],
                'hotelSupport' => [
                    'entryHotelDate' => '2020-01-01',
                ],
                'startDate' => $now->format('Y-m-d'),
                'agreement' => true,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Le suivi social est créé.');
    }

    public function testEditHotelSupportGroupIsSuccessful()
    {
        $user = $this->fixtures['userRoleUser'];
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->fixtures['supportGrpHotel1']->getId();
        $this->client->request('GET', "/support/$id/edit");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Édition du suivi');

        $now = new \DateTime();
        $this->client->submitForm('send', [
            'support' => [
                'service' => $this->fixtures['servicePash'],
                'subService' => $this->fixtures['subServicePash'],
                'device' => $this->fixtures['deviceHotel']->getCode(),
                'status' => SupportGroup::STATUS_IN_PROGRESS,
                'referent' => $user,
                'originRequest' => [
                    'orientationDate' => $now->format('Y-m-d'),
                    'organization' => $this->fixtures['siao95'],
                    'comment' => 'XXX',
                ],
                'hotelSupport' => [
                    'entryHotelDate' => '2020-01-01',
                    'emergencyActionRequest' => 1,
                    'reasonNoInclusion' => 1,
                    'evaluationDate' => $now->format('Y-m-d'),
                    'levelSupport' => 1,
                    'agreementDate' => $now->format('Y-m-d'),
                    'emergencyActionDone' => 1,
                    'emergencyActionPrecision' => 'XXX',
                    'departmentAnchor' => 95,
                    'recommendation' => 10,
                    'endSupportReason' => 1,
                ],
                'startDate' => $now->format('Y-m-d'),
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
