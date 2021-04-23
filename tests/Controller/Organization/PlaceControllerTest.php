<?php

namespace App\Tests\Controller;

use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PlaceControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $data;

    /** @var Service */
    protected $service;

    /** @var Place */
    protected $place;

    protected function setUp()
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PlaceFixturesTest.yaml',
        ]);

        $this->service = $this->data['service1'];
        $this->place = $this->data['place1'];
    }

    public function testSearchListPlacesIsSuccessful()
    {
        $this->createLogin($this->data['userAdmin']);

        $this->client->request('GET', '/places');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupes de places');

        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [
            'name' => 'Logement 666',
            'nbPlaces' => 6,
            'date[start]' => '2019-01-01',
            'date[end]' => '2020-01-01',
            'city' => 'Houilles',
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThanOrEqual(2, $crawler->filter('tr')->count());
    }

    public function testExportPlaceIsSuccessful()
    {
        $this->createLogin($this->data['userAdmin']);

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
        $this->assertContains('.spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreatePlaceIsFailed()
    {
        $this->createLogin($this->data['userAdmin']);

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
        $this->createLogin($this->data['userAdmin']);

        $id = $this->service->getId();
        $this->client->request('GET', "/admin/service/$id/place/new");

        // Fail
        $this->client->submitForm('send', [
            'place[name]' => 'Logement 666',
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
            'place[service]' => $this->data['service1'],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditPlaceIsSuccessful()
    {
        $this->createLogin($this->data['userAdmin']);

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
        $this->createLogin($this->data['userAdmin']);

        $id = $this->place->getId();
        $this->client->request('GET', "/admin/place/$id/delete");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->place->getService()->getName());
    }

    public function testDisablePlace()
    {
        $this->createLogin($this->data['userAdmin']);

        $id = $this->data['place1']->getId();
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
        $this->data = null;
    }
}
