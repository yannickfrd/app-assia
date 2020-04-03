<?php

namespace App\Tests\Controller;

use App\Entity\Person;
use Symfony\Component\DomCrawler\Crawler;
use App\Tests\AppTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Panther\PantherTestCase;

class PersonControllerTest extends PantherTestCase
{
    use FixturesTrait;
    use AppTestTrait;

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

        $this->createLogin($this->dataFixtures["userSuperAdmin"]);

        $this->client->followRedirects();

        $this->person = $this->dataFixtures["person1"];
    }

    public function testListPeoplePageIsUp()
    {
        /** @var Crawler */
        $crawler = $this->client->request("GET", $this->generateUri("people"));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Rechercher une personne");
    }

    public function testListPeoplePageWithResearch()
    {
        /** @var Crawler */
        $crawler = $this->client->request("GET", $this->generateUri("people"));

        $form = $crawler->selectButton("search")->form([
            "firstname" => "John",
            "lastname" => "DOE",
            "birthdate" => "1980-01-01",
            "phone" => "01 00 00 00 00",
            "gender" => 2
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("table tbody tr td a", "DOE");
    }

    public function testExportListPeople()
    {
        /** @var Crawler */
        $crawler = $this->client->request("GET", $this->generateUri("people"));

        $form = $crawler->selectButton("export")->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testAddPersonInGroupIsUp()
    {
        $this->client->request("GET", $this->generateUri("group_search_person", [
            "id" => $this->dataFixtures["groupPeople1"]->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Rechercher une personne");
    }

    // public function testAddPersonInGroup() // à tester via Panther
    // {
    //     /** @var Crawler */
    //     $crawler = $this->client->request("GET", $this->generateUri("group_search_person", [
    //         "id" => $this->dataFixtures["groupPeople1"]->getId()
    //     ]));

    //     $form = $crawler->selectButton("js-btn-confirm")->form([
    //         "role" => 1,
    //     ]);

    //     $this->client->submit($form);

    //     $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    // }


    public function testNewPersonIsUp()
    {
        $this->client->request("GET", $this->generateUri("person_new"));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Création d'une personne");
    }

    public function testFailToCreateNewPerson()
    {
        /** @var Crawler */
        $crawler = $this->client->request("GET", $this->generateUri("person_new"));

        $form = $crawler->selectButton("send")->form([
            "role_person_group[person][firstname]" => "Larissa",
            "role_person_group[person][lastname]" => "MULLER",
            "role_person_group[person][birthdate]" => "1987-05-09",
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists(".alert.alert-danger");
    }

    public function testCreatePersonThanExists()
    {
        /** @var Crawler */
        $crawler = $this->client->request("GET", $this->generateUri("person_new"));

        $form = $crawler->selectButton("send")->form([
            "role_person_group[person][firstname]" => "John",
            "role_person_group[person][lastname]" => "DOE",
            "role_person_group[person][birthdate]" => "1980-01-01",
            "role_person_group[person][gender]" => 2,
            "role_person_group[groupPeople][familyTypology]" => 2,
            "role_person_group[groupPeople][nbPeople]" => 1,
            "role_person_group[role]" => 5
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists(".alert.alert-danger");
        $this->assertSelectorTextContains(".form-error-message", "Cette personne existe déjà !");
    }

    public function testSuccessToCreateNewPerson()
    {
        /** @var Crawler */
        $crawler = $this->client->request("GET", $this->generateUri("person_new"));

        $form = $crawler->selectButton("send")->form([
            "role_person_group[person][firstname]" => "Larissa",
            "role_person_group[person][lastname]" => "MULLER",
            "role_person_group[person][birthdate]" => "1987-05-09",
            "role_person_group[person][gender]" => 1,
            "role_person_group[groupPeople][familyTypology]" => 1,
            "role_person_group[groupPeople][nbPeople]" => 1,
            "role_person_group[role]" => 5
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Groupe");
        $this->assertSelectorExists(".alert.alert-success");
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

    public function testFailToCreateNewPersonInGroup()
    {
        /** @var Crawler */
        $crawler = $this->client->request("GET", $this->generateUri("group_create_person", [
            "id" => $this->dataFixtures["groupPeople1"]->getId()
        ]));

        $form = $crawler->selectButton("send")->form([
            "person_role_person[person][firstname]" => "Larissa",
            "person_role_person[person][lastname]" => "MULLER",
            "person_role_person[person][birthdate]" => "1987-05-09",
            "person_role_person[person][gender]" => 1,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists(".alert.alert-danger");
    }

    public function testSuccessToCreateNewPersonInGroup()
    {
        /** @var Crawler */
        $crawler = $this->client->request("GET", $this->generateUri("group_create_person", [
            "id" => $this->dataFixtures["groupPeople1"]->getId()
        ]));

        $form = $crawler->selectButton("send")->form([
            "person_role_person[person][firstname]" => "Larissa",
            "person_role_person[person][lastname]" => "MULLER",
            "person_role_person[person][birthdate]" => "1987-05-09",
            "person_role_person[person][gender]" => 1,
            "person_role_person[role]" => 5
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Groupe");
        $this->assertSelectorExists(".alert.alert-success");
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

    public function testEditPersonInGroup() // Test via AJAX
    {
        /** @var Crawler */
        $crawler = $this->client->request("GET", $this->generateUri("group_person_show", [
            "id" => $this->dataFixtures["groupPeople1"]->getId(),
            "person_id" => $this->person->getId(),
            "slug" => $this->person->getSlug()
        ]));

        $form = $crawler->selectButton("updatePerson")->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        // $this->assertSelectorExists(".alert.alert-success");
    }

    public function testPersonShowIsUp()
    {
        $this->client->request("GET", $this->generateUri("person_show", [
            "id" => $this->person->getId(),
            "slug" => $this->person->getSlug()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", $this->person->getFullname());
    }

    // public function testEditPerson() // à tester avec Panther
    // {
    //     /** @var Crawler */
    //     $crawler = $this->client->request("GET", $this->generateUri("person_show", [
    //         "id" => $this->person->getId(),
    //     ]));

    //     $form = $crawler->selectButton("updatePerson")->form([]);

    //     $data = json_decode($this->client->getResponse()->getContent(), true);
    //     $this->assertSame("success", $data["alert"]);
    // }

    public function testFailAddNewGroupToPerson()
    {
        $this->client->request("POST", $this->generateUri("person_new_group", [
            "id" => $this->person->getId(),
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", $this->person->getFullname());
        $this->assertSelectorExists(".alert.alert-danger");
    }


    // public function testSuccessToAddNewGroupToPerson() // à tester avec Panther
    // {
    //     /** @var Crawler */
    //     $crawler =         $this->client->request("GET", $this->generateUri("person_show", [
    //         "id" => $this->person->getId(),
    //     ]));

    //     $form = $crawler->selectButton("js-btn-confirm")->form([
    //         "person_new_group[groupPeople][familyTypology]" => 1,
    //         "person_new_group[groupPeople][nbPeople]" => 1,
    //         "person_new_group[role]" => 1
    //     ]);

    //     $this->client->submit($form);

    //     $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    //     $this->assertSelectorTextContains("h1", "Groupe");
    //     $this->assertSelectorExists(".alert.alert-success");
    // }


    public function testSearchPersonWithOneResult()
    {
        $this->client->request("GET", $this->generateUri("search_person", [
            "search" => "John Doe",
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame(1, $data["nb_results"]);
    }

    public function testDeletePerson()
    {
        $this->client->request("GET", $this->generateUri("person_delete", [
            "id" => $this->person->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains(".alert.alert-warning", "La personne a été supprimée.");
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

    protected function tearDown()
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
