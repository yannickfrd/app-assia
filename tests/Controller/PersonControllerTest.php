<?php

namespace App\Tests\Controller;

use App\Entity\Person;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Panther\Client;

class PersonControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var Client */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    protected $userAdmin;

    /** @var Person */
    protected $person;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/PersonFixturesTest.yaml',
        ]);

        $this->userAdmin = $this->dataFixtures['userRoleAdmin'];
        $this->user = $this->dataFixtures['userRoleUser'];
        $this->person = $this->dataFixtures['person1'];
    }

    public function testListPeoplePageIsUp()
    {
        $this->createLogin($this->user);

        $this->client->request('GET', $this->generateUri('people'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Rechercher une personne');
    }

    // public function testListPeoplePageWithResearch()
    // {
    //     $this->createLogin($this->user);

    //     /** @var Crawler */
    //     $crawler = $this->client->request('GET', $this->generateUri('people'));

    //     $form = $crawler->selectButton('search')->form([
    //         'firstname' => 'John',
    //         'lastname' => 'DOE',
    //         'birthdate' => '1980-01-01',
    //     ]);

    //     $this->client->submit($form);

    //     $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    //     $this->assertSelectorTextContains('table tbody tr td a', 'DOE');
    // }

    public function testAddPersonInGroupIsUp()
    {
        $this->createLogin($this->user);

        $this->client->request('GET', $this->generateUri('group_search_person', [
            'id' => $this->dataFixtures['groupPeople1']->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'GroupeAjouter une personne');
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
        $this->createLogin($this->user);

        $this->client->request('GET', $this->generateUri('person_new'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', "Création d'une personne");
    }

    public function testFailToCreateNewPerson()
    {
        $this->createLogin($this->user);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('person_new'));

        $form = $crawler->selectButton('send')->form([
            'role_person_group[person][firstname]' => 'Larissa',
            'role_person_group[person][lastname]' => 'MULLER',
            'role_person_group[person][birthdate]' => '1987-05-09',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testCreatePersonWhoExists()
    {
        $this->createLogin($this->user);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('person_new'));

        $form = $crawler->selectButton('send')->form([
            'role_person_group[person][firstname]' => 'John',
            'role_person_group[person][lastname]' => 'DOE',
            'role_person_group[person][birthdate]' => '1980-01-01',
            'role_person_group[person][gender]' => 2,
            'role_person_group[groupPeople][familyTypology]' => 2,
            'role_person_group[groupPeople][nbPeople]' => 1,
            'role_person_group[role]' => 5,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
        $this->assertSelectorTextContains('.form-error-message', 'Cette personne existe déjà !');
    }

    public function testSuccessToCreateNewPerson()
    {
        $this->createLogin($this->user);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('person_new'));

        $form = $crawler->selectButton('send')->form([
            'role_person_group[person][firstname]' => 'Larissa',
            'role_person_group[person][lastname]' => 'MULLER',
            'role_person_group[person][birthdate]' => '1987-05-09',
            'role_person_group[person][gender]' => 1,
            'role_person_group[groupPeople][familyTypology]' => 1,
            'role_person_group[groupPeople][nbPeople]' => 1,
            'role_person_group[role]' => 5,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupe');
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testNewPersonInGroupIsUp()
    {
        $this->createLogin($this->user);

        $this->client->request('GET', $this->generateUri('group_create_person', [
            'id' => $this->dataFixtures['groupPeople1']->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', "Création d'une personne");
        $this->assertSelectorTextContains('div.container nav ol.breadcrumb li:last-child', 'Création d\'une personne');
    }

    public function testFailToCreateNewPersonInGroup()
    {
        $this->createLogin($this->user);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('group_create_person', [
            'id' => $this->dataFixtures['groupPeople1']->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([
            'person_role_person[person][firstname]' => 'Larissa',
            'person_role_person[person][lastname]' => 'MULLER',
            'person_role_person[person][birthdate]' => '1987-05-09',
            'person_role_person[person][gender]' => 1,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testSuccessToCreateNewPersonInGroup()
    {
        $this->createLogin($this->user);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('group_create_person', [
            'id' => $this->dataFixtures['groupPeople1']->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([
            'person_role_person[person][firstname]' => 'Larissa',
            'person_role_person[person][lastname]' => 'MULLER',
            'person_role_person[person][birthdate]' => '1987-05-09',
            'person_role_person[person][gender]' => 1,
            'person_role_person[role]' => 5,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupe');
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditPersonInGroupIsUp()
    {
        $this->createLogin($this->user);

        $this->client->request('GET', $this->generateUri('group_person_show', [
            'id' => $this->dataFixtures['groupPeople1']->getId(),
            'person_id' => $this->person->getId(),
            'slug' => $this->person->getSlug(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->person->getFullname());
    }

    public function testPersonShowIsUp()
    {
        $this->createLogin($this->user);

        $this->client->request('GET', $this->generateUri('person_show', [
            'id' => $this->person->getId(),
            'slug' => $this->person->getSlug(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->person->getFullname());
    }

    public function testFailAddNewGroupToPerson()
    {
        $this->createLogin($this->user);

        $this->client->request('POST', $this->generateUri('person_new_group', [
            'id' => $this->person->getId(),
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->person->getFullname());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testSearchPersonWithOneResult()
    {
        $this->createLogin($this->user);

        $this->client->request('GET', $this->generateUri('search_person', [
            'search' => 'John Doe',
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame(1, count($data['people']));
    }

    public function testDeletePersonWithRoleUser()
    {
        $this->createLogin($this->user);

        $this->client->request('GET', $this->generateUri('person_delete', [
            'id' => $this->person->getId(),
        ]));

        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testDeletePersonWithRoleAdmin()
    {
        $this->createLogin($this->userAdmin);

        $this->client->request('GET', $this->generateUri('person_delete', [
            'id' => $this->person->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'La personne est supprimée.');
    }

    public function testSearchPersonWithResults()
    {
        $this->createLogin($this->user);

        $this->client->request('GET', $this->generateUri('search_person', [
            'search' => '',
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(5, count($data['people']));
    }

    public function testSearchPersonWithoutResult()
    {
        $this->createLogin($this->user);

        $this->client->request('GET', $this->generateUri('search_person', [
            'search' => 'xxx',
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame(0, count($data['people']));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
