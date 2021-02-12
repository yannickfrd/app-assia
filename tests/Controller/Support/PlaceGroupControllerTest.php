<?php

namespace App\Tests\Controller;

use App\Entity\Support\PlaceGroup;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class PlaceGroupControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var PlaceGroup */
    protected $placeGroup;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/PlaceGroupFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->supportGroup = $this->dataFixtures['supportGroup1'];
        $this->placeGroup = $this->dataFixtures['accomGroup1'];
    }

    public function testListSupportPlacesIsUp()
    {
        $this->client->request('GET', $this->generateUri('support_places', [
            'id' => $this->supportGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');
    }

    public function testNewPlaceGroupIsUp()
    {
        $this->client->request('GET', $this->generateUri('support_place_new', [
            'id' => $this->supportGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');
    }

    public function testSuccessToCreateNewPlaceGroup()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('support_place_new', [
            'id' => $this->supportGroup->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([
            'place_group[place]' => 1,
            'place_group[startDate]' => (new \DateTime())->format('Y-m-d'),
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->assertSelectorTextContains('.alert.alert-success', 'L\'hébergement est créé.');
    }

    public function testFailToCreateNewPlaceGroup()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('support_place_new', [
            'id' => $this->supportGroup->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([
            // 'place_group[place]' => 1,
            'place_group[startDate]' => null,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testEditPlaceGroupIsUp()
    {
        $this->client->request('POST', $this->generateUri('support_place_edit', [
            'id' => $this->placeGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');
    }

    public function testEditPlaceGroup()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('support_place_edit', [
            'id' => $this->placeGroup->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testAddPeopleInPlace()
    {
        $this->client->request('GET', $this->generateUri('support_group_people_place_add_people', [
            'id' => $this->placeGroup->getId(),
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');
        $this->assertSelectorTextContains('.alert.alert-success', 'à la prise en charge.');
    }

    public function testDeletePlaceGroup()
    {
        $this->client->request('GET', $this->generateUri('support_group_people_place_delete', [
            'id' => $this->placeGroup->getId(),
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'La prise en charge est supprimée.');
    }

    public function testDeletePlacePerson()
    {
        $this->client->request('GET', $this->generateUri('support_person_place_delete', [
            'id' => $this->dataFixtures['accomPerson1']->getId(),
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');
        $this->assertSelectorExists('.alert.alert-warning');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
