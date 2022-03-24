<?php

namespace App\Tests\Controller\Support;

use App\Entity\Support\SupportGroup;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;

class AvdlControllerTest extends WebTestCase
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

        /* @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/avdl_support_fixtures_test.yaml',
        ]);

        $this->supportGroup = $this->fixtures['support_group_avdl'];
    }

    public function testSearchAvdlSupportsIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/avdl-supports');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Suivis AVDL');

        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [
            'diagOrSupport' => 1,
            'supportType' => [1, 2],
        ], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Suivis AVDL');
        $this->assertGreaterThanOrEqual(2, $crawler->filter('tr')->count());
    }

    public function testExportAvdlSupportsIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/avdl-supports');

        // Export without result
        $this->client->submitForm('export', [
            'supportDates' => 1,
            'date[start]' => (new \DateTime())->modify('+1 year')->format('Y-m-d'),
        ], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-warning', 'Aucun résultat à exporter.');

        // Export with results
        $this->client->submitForm('export', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('.spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreateAvdlSupportGroupIsSuccessful(): void
    {
        $user = $this->fixtures['john_user'];
        $this->client->loginUser($user);

        $id = $this->fixtures['people_group3']->getId();
        $this->client->request('POST', "/people-group/$id/new-support", [
            'support' => [
                'service' => $this->fixtures['service_avdl'],
                'device' => $this->fixtures['device_avdl']->getCode(),
                'referent' => $user,
            ],
        ]);

        $now = new \DateTime();
        $this->client->submitForm('send', [
            'support' => [
                'service' => $this->fixtures['service_avdl'],
                'device' => $this->fixtures['device_avdl']->getCode(),
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

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-success', 'Le suivi social est créé.');
    }

    public function testEditAvdlSupportGroupIsSuccessful(): void
    {
        $user = $this->fixtures['john_user'];
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['support_group_avdl']->getId();
        $this->client->request('GET', "/support/$id/edit");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Édition du suivi');

        $now = new \DateTime();
        $this->client->submitForm('send', [
            'support' => [
                'service' => $this->fixtures['service_avdl'],
                'device' => $this->fixtures['device_avdl']->getCode(),
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

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-success', 'Le suivi social est mis à jour.');

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
