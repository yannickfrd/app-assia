<?php

namespace App\Tests\Controller;

use App\Entity\AccommodationGroup;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class AccommodationGroupControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var AccommodationGroup */
    protected $accommodationGroup;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/AccommodationGroupFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->supportGroup = $this->dataFixtures['supportGroup1'];
        $this->accommodationGroup = $this->dataFixtures['accomGroup1'];
    }

    public function testListSupportAccommodationsIsUp()
    {
        $this->client->request('GET', $this->generateUri('support_accommodations', [
            'id' => $this->supportGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');
    }

    public function testNewAccommodationGroupIsUp()
    {
        $this->client->request('GET', $this->generateUri('support_accommodation_new', [
            'id' => $this->supportGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');
    }

    public function testSuccessToCreateNewAccommodationGroup()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('support_accommodation_new', [
            'id' => $this->supportGroup->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([
            'accommodation_group[accommodation]' => 1,
            'accommodation_group[startDate]' => (new \DateTime())->format('Y-m-d'),
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->assertSelectorTextContains('.alert.alert-success', 'L\'hébergement est créé.');
    }

    public function testFailToCreateNewAccommodationGroup()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('support_accommodation_new', [
            'id' => $this->supportGroup->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([
            // 'accommodation_group[accommodation]' => 1,
            'accommodation_group[startDate]' => null,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testEditAccommodationGroupIsUp()
    {
        $this->client->request('POST', $this->generateUri('support_accommodation_edit', [
            'id' => $this->accommodationGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');
    }

    public function testEditAccommodationGroup()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('support_accommodation_edit', [
            'id' => $this->accommodationGroup->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testAddPeopleInAccommodation()
    {
        $this->client->request('GET', $this->generateUri('support_group_people_accommodation_add_people', [
            'id' => $this->accommodationGroup->getId(),
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');
        $this->assertSelectorTextContains('.alert.alert-success', 'à la prise en charge.');
    }

    public function testDeleteAccommodationGroup()
    {
        $this->client->request('GET', $this->generateUri('support_group_people_accommodation_delete', [
            'id' => $this->accommodationGroup->getId(),
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'La prise en charge est supprimée.');
    }

    public function testDeleteAccommodationPerson()
    {
        $this->client->request('GET', $this->generateUri('support_person_accommodation_delete', [
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
