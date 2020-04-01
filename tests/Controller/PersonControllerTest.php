<?php

namespace App\Tests\Controller;

use App\Entity\Person;
use Symfony\Component\DomCrawler\Crawler;
use App\Tests\Controller\ControllerTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ControllerTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var Person */
    protected $person;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/UserFixturesTest.yaml",
            dirname(__DIR__) . "/DataFixturesTest/PersonFixturesTest.yaml"
        ]);

        $this->createLoggedUser($this->dataFixtures);

        $this->person = $this->dataFixtures["person1"];
    }

    public function testListPeoplePageIsUp()
    {
        /** @var Crawler */
        $crawler = $this->client->request("GET", $this->generateUri("people"));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Rechercher une personne");
    }

    public function testSearchPersonToAddIsUp()
    {
        $this->client->request("GET", $this->generateUri("group_search_person", [
            "id" => $this->dataFixtures["groupPeople1"]->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Rechercher une personne");
    }

    public function testNewPersonIsUp()
    {
        $this->client->request("GET", $this->generateUri("person_new"));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Création d'une personne");
    }

    public function testNewPersonInGroupIsUp()
    {
        $this->client->request("GET", $this->generateUri("group_create_person", [
            "id" => $this->dataFixtures["groupPeople1"]->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Création d'une personne");
        $this->assertSelectorTextContains("div.container nav ol.breadcrumb li:last-child", "Fiche individuelle");
    }

    public function testEditPersonInGroupIsUp()
    {
        $this->client->request("GET", $this->generateUri("group_person_show", [
            "id" => $this->dataFixtures["groupPeople1"]->getId(),
            "person_id" => $this->person->getId(),
            "slug" => $this->person->getSlug()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", $this->person->getFullname());
    }

    // public function testEditPersonIsUp()
    // {
    //     $this->client->request("POST", $this->generateUri("person_edit_ajax", [
    //         "id" => $this->dataFixtures["person1"]->getId(),
    //     ]));

    //     $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    // }

    public function testPersonShowIsUp()
    {
        $crawler  = $this->client->request("GET", $this->generateUri("person_show", [
            "id" => $this->person->getId(),
            "slug" => $this->person->getSlug()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", $this->person->getFullname());
    }


    public function testFailAddNewGroupToPerson()
    {
        $crawler = $this->client->request("POST", $this->generateUri("person_new_group", [
            "id" => $this->person->getId(),
        ]));

        $this->client->followRedirect();

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", $this->person->getFullname());
        $this->assertSelectorExists(".alert.alert-danger");
    }

    public function testSearchPersonWithOneResult()
    {
        $this->client->request("GET", $this->generateUri("search_person", [
            "search" => "John Doe",
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame(1, $data["nb_results"]);
    }

    public function testSearchPersonWithResults()
    {
        $this->client->request("GET", $this->generateUri("search_person", [
            "search" => "",
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(5, $data["nb_results"]);
    }
    public function testSearchPersonWithoutResult()
    {
        $this->client->request("GET", $this->generateUri("search_person", [
            "search" => "xxx",
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame(0, $data["nb_results"]);
    }
}
