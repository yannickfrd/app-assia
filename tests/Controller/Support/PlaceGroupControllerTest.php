<?php

namespace App\Tests\Controller\Support;

use App\Tests\AppTestTrait;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\SupportGroup;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class PlaceGroupControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $data;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var PlaceGroup */
    protected $placeGroup;

    protected function setUp(): void
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PlaceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PlaceGroupFixturesTest.yaml',
        ]);

        $this->createLogin($this->data['userRoleUser']);

        $this->supportGroup = $this->data['supportGroup1'];
        $this->placeGroup = $this->data['plGroup1'];
    }

    public function testListSupportPlacesIsUp()
    {
        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/places");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');
    }

    public function testCreatePlaceGroupIsSuccessful()
    {
        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/place/new");

        // Page is up
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');

        // Page is up
        $this->client->submitForm('send', [
            'place_group[place]' => 1,
            'place_group[startDate]' => (new \DateTime())->format('Y-m-d'),
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->assertSelectorTextContains('.alert.alert-success', 'L\'hébergement est créé.');
    }

    public function testCreateNewPlaceGroupIsFailed()
    {
        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/place/new");

        $this->client->submitForm('send', [
            'place_group[startDate]' => null,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testEditPlaceGroupIsSuccessful()
    {
        $id = $this->placeGroup->getId();
        $this->client->request('GET', "/support/place_group/$id");

        // Page is up
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');

        // Edit is successful
        $this->client->submitForm('send');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testAddPeopleInPlaceIsSuccessful()
    {
        $id = $this->data['plPerson1']->getId();
        $this->client->request('GET', "/support/person-place/$id/delete");

        $id = $this->placeGroup->getId();
        $this->client->request('GET', "/support/group_people_place/$id/add_people");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');
        $this->assertSelectorTextContains('.alert.alert-success', 'à la prise en charge.');
    }

    public function testDeletePlaceGroupIsSuccessful()
    {
        $id = $this->placeGroup->getId();
        $this->client->request('GET', "/support/group-people-place/$id/delete");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'La prise en charge est supprimée.');
    }

    public function testDeletePlacePersonIsSuccessful()
    {
        $id = $this->data['plPerson1']->getId();
        $this->client->request('GET', "/support/person-place/$id/delete");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-warning');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->data = null;

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();

    }
}
