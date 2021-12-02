<?php

namespace App\Tests\Controller\Organization;

use App\Tests\AppTestTrait;
use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class PlaceControllerTest extends WebTestCase
{
    use AppTestTrait;

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

        $this->client = $this->createClient();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PlaceFixturesTest.yaml',
        ]);

        $this->service = $this->fixtures['service1'];
        $this->place = $this->fixtures['place1'];
    }

    public function testSearchListPlacesIsSuccessful()
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $this->client->request('GET', '/places');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupes de places');

        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [
            'name' => 'Logement test',
            'nbPlaces' => 6,
            'service' => [
                'services' => $this->service->getId(),
                'devices' => $this->fixtures['device1'],
            ],
            'date[start]' => '2019-01-01',
            'date[end]' => '2020-01-01',
            'city' => 'Houilles',
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThanOrEqual(2, $crawler->filter('tr')->count());
    }

    public function testExportPlaceIsSuccessful()
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $this->client->request('GET', '/places');

        // Export without result
        $this->client->submitForm('export', [
            'name' => 'Logement inconnu',
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Export with results
        $this->client->request('GET', '/places');

        $this->client->submitForm('export', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('.spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreatePlaceIsFailed()
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $id = $this->service->getId();
        $this->client->request('GET', "/admin/service/$id/place/new");

        // Page is up
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau groupe de places');

        // Create is failed
        $this->client->submitForm('send');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testCreateNewPlaceIsSuccessful()
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $id = $this->service->getId();
        $this->client->request('GET', "/admin/service/$id/place/new");

        // Fail
        $this->client->submitForm('send', [
            'place[name]' => 'Logement test',
            'place[service]' => $this->service->getId(),
            'place[nbPlaces]' => 6,
            'place[startDate]' => '2019-01-01',
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');

        // Success
        $this->client->submitForm('send', [
            'place[name]' => 'Nouveau logement',
            'place[nbPlaces]' => 6,
            'place[startDate]' => '2019-01-01',
            'place[endDate]' => '2020-01-01',
            'place[location][city]' => 'Houilles',
            'place[location][zipcode]' => '78 800',
            'place[location][address]' => 'xxx',
            'place[service]' => $this->fixtures['service1'],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditPlaceIsSuccessful()
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $id = $this->place->getId();
        $this->client->request('GET', "/place/$id");

        // Page is up
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->place->getName());

        // Edit is successful
        $this->client->submitForm('send');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testDeletePlace()
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $id = $this->place->getId();
        $this->client->request('GET', "/admin/place/$id/delete");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->place->getService()->getName());
    }

    public function testDisablePlace()
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $id = $this->fixtures['place1']->getId();
        $this->client->request('GET', "/admin/place/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'est désactivé');

        $this->client->request('GET', "/admin/place/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'est ré-activé');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        $this->client = null;
        $this->fixtures = null;
    }
}
