<?php

namespace App\Tests\Controller\Organization;

use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlaceControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var Service */
    protected $service;

    /** @var Place */
    protected $place;

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
            dirname(__DIR__).'/../fixtures/place_fixtures_test.yaml',
        ]);

        $this->service = $this->fixtures['service2'];
        $this->place = $this->fixtures['place1'];
    }

    public function testSearchPlacesIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_admin']);

        $this->client->request('GET', '/places');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Groupes de places');

        $crawler = $this->client->submitForm('search', [
            'name' => 'Logement test',
            'nbPlaces' => 6,
            'service' => [
                'services' => $this->service->getId(),
            ],
            'date[start]' => '2019-01-01',
            'date[end]' => '2020-01-01',
            'city' => 'Houilles',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThanOrEqual(1, $crawler->filter('tr')->count());
    }

    public function testExportPlaceIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_admin']);

        $this->client->request('GET', '/places');

        // Export without result
        $this->client->submitForm('export', [
            'name' => 'Logement inconnu',
        ], 'GET');

        $this->assertResponseIsSuccessful();

        // Export with results
        $this->client->request('GET', '/places');

        $this->client->submitForm('export', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('.spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreatePlaceIsFailed(): void
    {
        $this->client->loginUser($this->fixtures['user_admin']);

        $id = $this->service->getId();
        $this->client->request('GET', "/admin/service/$id/place/new");

        // Page is up
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Nouveau groupe de places');

        // Create is failed
        $this->client->submitForm('send');

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorExists('.toast.alert-danger');
    }

    public function testCreateNewPlaceIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_admin']);

        $id = $this->service->getId();
        $this->client->request('GET', "/admin/service/$id/place/new");

        // Fail
        $this->client->submitForm('send', [
            'place[name]' => null,
            'place[service]' => $this->service->getId(),
            'place[nbPlaces]' => 6,
            'place[startDate]' => '2019-01-01',
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorExists('.toast.alert-danger');

        // Success
        $this->client->submitForm('send', [
            'place[name]' => 'Nouveau logement',
            'place[nbPlaces]' => 6,
            'place[startDate]' => '2019-01-01',
            'place[endDate]' => '2020-01-01',
            'place[location][city]' => 'Houilles',
            'place[location][zipcode]' => '78 800',
            'place[location][address]' => 'xxx',
            'place[service]' => $this->service->getId(),
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.toast.alert-success');
    }

    public function testEditPlaceIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $id = $this->place->getId();
        $this->client->request('GET', "/place/$id");

        // Page is up
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->place->getName());

        // Edit is successful
        $this->client->submitForm('send');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.toast.alert-success');
    }

    public function testDeletePlace(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $id = $this->place->getId();
        $this->client->request('GET', "/place/$id/delete");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->place->getService()->getName());
    }

    public function testDisablePlace(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $id = $this->fixtures['place1']->getId();
        $this->client->request('GET', "/place/$id/disable");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-warning', 'est désactivé');

        $this->client->request('GET', "/place/$id/disable");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-success', 'est ré-activé');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
