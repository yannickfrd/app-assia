<?php

namespace App\Tests\Controller\Support;

use App\Entity\Support\SupportGroup;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;

class HotelControllerTest extends WebTestCase
{
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

        $this->client = static::createClient();
        $this->client->followRedirects();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/hotel_support_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
        ]);

        $this->supportGroup = $this->fixtures['support_group_hotel1'];
    }

    public function testSearchHotelSupportsIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        // Page is up
        $this->client->request('GET', '/hotel-supports');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Suivis PASH');

        // Search is successful
        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Suivis PASH');
        $this->assertGreaterThanOrEqual(2, $crawler->filter('tr')->count());
    }

    public function testExportHotelSupportsIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/hotel-supports');

        // Export without result
        $this->client->submitForm('export', [
            'supportDates' => 1,
            'date[start]' => (new \DateTime())->modify('+1 year')->format('Y-m-d'),
        ], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-warning', 'Aucun résultat à exporter.');

        // Export with results
        $this->client->submitForm('export', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('.spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreateHotelSupportGroupIsSuccessful(): void
    {
        $user = $this->fixtures['john_user'];
        $this->client->loginUser($user);

        $id = $this->fixtures['people_group3']->getId();
        $this->client->request('POST', "/people-group/$id/new-support", [
            'support' => [
                'service' => $this->fixtures['service_pash'],
                'device' => $this->fixtures['device_hotel']->getCode(),
                'referent' => $user,
            ],
        ]);

        $now = new \DateTime();
        $this->client->submitForm('send', [
            'support' => [
                'service' => $this->fixtures['service_pash'],
                'device' => $this->fixtures['device_hotel']->getCode(),
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

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-success', 'Le suivi a été créé');
    }

    public function testEditHotelSupportGroupIsSuccessful(): void
    {
        $user = $this->fixtures['john_user'];
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['support_group_hotel1']->getId();
        $this->client->request('GET', "/support/$id/edit");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Édition');

        $now = new \DateTime();
        $this->client->submitForm('send', [
            'support' => [
                'service' => $this->fixtures['service_pash'],
                'subService' => $this->fixtures['sub_service_pash'],
                'device' => $this->fixtures['device_hotel']->getCode(),
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
                ],
                'startDate' => $now->format('Y-m-d'),
                'agreement' => true,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-success', 'Le suivi a été mis à jour.');

        $this->client->request('GET', "/support/$id/show");
        $this->assertResponseIsSuccessful();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();
    }
}
