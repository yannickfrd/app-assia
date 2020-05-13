<?php

namespace App\Tests\Controller;

use App\Entity\Accommodation;
use App\Entity\Service;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AccommodationControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var Service */
    protected $service;

    /** @var Accommodation */
    protected $accommodation;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/AccommodationFixturesTest.yaml',
        ]);

        $this->service = $this->dataFixtures['service'];
        $this->accommodation = $this->dataFixtures['accommodation1'];
    }

    public function testListAccommodationsIsUp()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('admin_accommodations'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupes de places');
    }

    public function testListAccommodationPageWithReasearch()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('admin_accommodations'));

        $form = $crawler->selectButton('search')->form([
            'name' => 'Logement 666',
            'nbPlaces' => 6,
            'date[start]' => '2019-01-01',
            'date[end]' => '2020-01-01',
            'city' => 'Houilles',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('table tbody tr td', 'Logement 666');
    }

    public function testExportListAccommodationWithResults()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $crawler = $this->client->request('GET', $this->generateUri('admin_accommodations'));

        $form = $crawler->selectButton('export')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testExportListAccommodationWithoutResults()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $crawler = $this->client->request('GET', $this->generateUri('admin_accommodations'));

        $form = $crawler->selectButton('export')->form([
            'name' => 'Logement inconnu',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testNewAccommodationIsUp()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('service_accommodation_new', [
            'id' => $this->service->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau groupe de places');
    }

    public function testFailToCreateAccommodation()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('service_accommodation_new', [
            'id' => $this->service->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([
            'accommodation[name]' => 'Nouveau logement',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testCreateAccommodationThatExists()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('service_accommodation_new', [
            'id' => $this->service->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([
            'accommodation[name]' => 'Logement 666',
            'accommodation[service]' => $this->service,
            'accommodation[nbPlaces]' => 6,
            'accommodation[startDate]' => '2019-01-01',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testSuccessToCreateNewAccommodation()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('service_accommodation_new', [
            'id' => $this->service->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([
            'accommodation[name]' => 'Nouveau logement',
            'accommodation[nbPlaces]' => 6,
            'accommodation[startDate]' => '2019-01-01',
            'accommodation[endDate]' => '2020-01-01',
            'accommodation[location][city]' => 'Houilles',
            'accommodation[location][zipcode]' => '78 800',
            'accommodation[location][address]' => 'xxx',
            'accommodation[service]' => 1,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditAccommodationisUp()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('accommodation_edit', [
            'id' => $this->accommodation->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->accommodation->getName());
    }

    public function testSuccessToEditAccommodation()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('accommodation_edit', [
            'id' => $this->service->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testDeleteAccommodation()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('admin_accommodation_delete', [
            'id' => $this->accommodation->getId(),
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->accommodation->getService()->getName());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
