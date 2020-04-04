<?php

namespace App\Tests\Controller;

use App\Entity\AccommodationGroup;
use Symfony\Component\DomCrawler\Crawler;
use App\Tests\AppTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
            dirname(__DIR__) . "/DataFixturesTest/AccommodationGroupFixturesTest.yaml",
        ]);

        $this->createLogin($this->dataFixtures["userSuperAdmin"]);

        $this->supportGroup = $this->dataFixtures["supportGroup1"];
        $this->accommodationGroup = $this->dataFixtures["accomGroup1"];
    }

    public function testListSupportAccommodationsIsUp()
    {
        /** @var Crawler */
        $crawler = $this->client->request("GET", $this->generateUri("support_accommodations", [
            "id" => $this->supportGroup->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Logement/hébergement");
    }

    public function testNewAccommodationGroupIsUp()
    {
        $this->client->request("GET", $this->generateUri("support_accommodation_new", [
            "id" => $this->supportGroup->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Logement/hébergement");
    }

    public function testEditAccommodationGroupIsUp()
    {
        $this->client->request("POST", $this->generateUri("support_accommodation_edit", [
            "id" => $this->accommodationGroup->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Logement/hébergement");
    }

    public function testAddPeopleInAccommodation()
    {
        $this->client->request("GET", $this->generateUri("support_group_people_accommodation_add_people", [
            "id" => $this->accommodationGroup->getId()
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Logement/hébergement");
        $this->assertSelectorTextContains(".alert.alert-success", "Les personnes ont été ajoutées à la prise en charge.");
    }

    public function testDeleteAccommodationGroup()
    {
        $this->client->request("GET", $this->generateUri("support_group_people_accommodation_delete", [
            "id" => $this->accommodationGroup->getId()
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains(".alert.alert-warning", "La prise en charge a été supprimé.");
    }
    public function testDeleteAccommodationPerson()
    {
        $this->client->request("GET", $this->generateUri("support_person_accommodation_delete", [
            "id" => $this->dataFixtures["accomPerson1"]->getId()
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Logement/hébergement");
        $this->assertSelectorExists(".alert.alert-warning");
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
