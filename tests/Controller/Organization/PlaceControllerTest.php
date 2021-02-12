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
    protected $dataFixtures;

    /** @var Service */
    protected $service;

    /** @var Place */
    protected $place;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PlaceFixturesTest.yaml',
        ]);

        $this->service = $this->dataFixtures['service1'];
        $this->place = $this->dataFixtures['place1'];
    }

    public function testListPlacesIsUp()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        $this->client->request('GET', $this->generateUri('places'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupes de places');
    }

    public function testListPlacePageWithReasearch()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('places'));

        $form = $crawler->selectButton('search')->form([
            'name' => 'Logement 666',
            'nbPlaces' => 6,
            'date[start]' => '2019-01-01',
            'date[end]' => '2020-01-01',
            'city' => 'Houilles',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('table tbody tr td:nth-child(2)', 'Logement 666');
    }

    public function testExportListPlaceWithResults()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        $crawler = $this->client->request('GET', $this->generateUri('places'));

        $form = $crawler->selectButton('export')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testExportListPlaceWithoutResults()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        $crawler = $this->client->request('GET', $this->generateUri('places'));

        $form = $crawler->selectButton('export')->form([
            'name' => 'Logement inconnu',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testNewPlaceIsUp()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        $this->client->request('GET', $this->generateUri('service_place_new', [
            'id' => $this->service->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau groupe de places');
    }

    public function testFailToCreatePlace()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('service_place_new', [
            'id' => $this->service->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([
            // 'place[name]' => 'Nouveau logement',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testCreatePlaceThatExists()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('service_place_new', [
            'id' => $this->service->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([
            'place[name]' => 'Logement 666',
            'place[service]' => $this->service,
            'place[nbPlaces]' => 6,
            'place[startDate]' => '2019-01-01',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testSuccessToCreateNewPlace()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('service_place_new', [
            'id' => $this->service->getId(),
            ]));

        $form = $crawler->selectButton('send')->form([
            'place[name]' => 'Nouveau logement',
            'place[nbPlaces]' => 6,
            'place[startDate]' => '2019-01-01',
            'place[endDate]' => '2020-01-01',
            'place[location][city]' => 'Houilles',
            'place[location][zipcode]' => '78 800',
            'place[location][address]' => 'xxx',
            'place[service]' => 1,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditPlaceisUp()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        $this->client->request('GET', $this->generateUri('place_edit', [
            'id' => $this->place->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->place->getName());
    }

    public function testSuccessToEditPlace()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('place_edit', [
            'id' => $this->service->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testDeletePlace()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        $this->client->request('GET', $this->generateUri('admin_place_delete', [
            'id' => $this->place->getId(),
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->place->getService()->getName());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
