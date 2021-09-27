<?php

namespace App\Tests\Controller\People;

use App\Entity\People\Person;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class PersonControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $data;

    protected $userAdmin;

    /** @var Person */
    protected $person;

    protected function setUp(): void
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
        ]);

        $this->userAdmin = $this->data['userAdmin'];
        $this->user = $this->data['userRoleUser'];
        $this->person = $this->data['person1'];
    }

    public function testPeoplePageIsUp()
    {
        $this->createLogin($this->user);

        $this->client->request('GET', '/people');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Rechercher une personne');
    }

    public function testSearchInPeoplePageIsSuccessful()
    {
        $this->createLogin($this->user);

        $this->client->request('POST', '/people/search', [
            'firstname' => 'John',
            'lastname' => 'DOE',
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('DOE', $content['people'][0]['lastname']);
    }

    public function testAddPersonInGroupIsUp()
    {
        $this->createLogin($this->user);

        $id = $this->data['peopleGroup1']->getId();
        $this->client->request('GET', "/group/$id/search_person");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Ajouter une personne');
    }

    public function testNewPersonIsUp()
    {
        $this->createLogin($this->user);

        $this->client->request('GET', '/person/new');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Création d\'une personne');
    }

    public function testCreateNewPersonIsFailed()
    {
        $this->createLogin($this->user);

        $this->client->request('GET', '/person/new');

        $this->client->submitForm('send', [
            'role_person_group[person][firstname]' => 'Larissa',
            'role_person_group[person][lastname]' => 'MULLER',
            'role_person_group[person][birthdate]' => '1987-05-09',
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testCreatePersonWhoExistsIsFailed()
    {
        $this->createLogin($this->user);

        $this->client->request('GET', '/person/new');

        $this->client->submitForm('send', [
            'role_person_group[person][firstname]' => 'John',
            'role_person_group[person][lastname]' => 'DOE',
            'role_person_group[person][birthdate]' => '1980-01-01',
            'role_person_group[person][gender]' => Person::GENDER_FEMALE,
            'role_person_group[peopleGroup][familyTypology]' => 2,
            'role_person_group[peopleGroup][nbPeople]' => 1,
            'role_person_group[role]' => 5,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
        $this->assertSelectorTextContains('.form-error-message', 'Cette personne existe déjà !');
    }

    public function testNewPersonInGroupIsUp()
    {
        $this->createLogin($this->user);

        $id = $this->data['peopleGroup1']->getId();
        $this->client->request('GET', "/group/$id/person/new");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Création d\'une personne');
        $this->assertSelectorTextContains('div.container nav ol.breadcrumb li:last-child', 'Création d\'une personne');
    }

    public function testCreateNewPersonIsSuccessful()
    {
        $this->createLogin($this->user);

        $this->client->request('GET', '/person/new');

        $this->client->submitForm('send', [
            'role_person_group[person][firstname]' => 'Larissa',
            'role_person_group[person][lastname]' => 'MULLER',
            'role_person_group[person][birthdate]' => '1987-05-09',
            'role_person_group[person][gender]' => 1,
            'role_person_group[peopleGroup][familyTypology]' => 1,
            'role_person_group[peopleGroup][nbPeople]' => 1,
            'role_person_group[role]' => 5,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupe');
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testAddPersonInGroupIsFailed()
    {
        $this->createLogin($this->user);

        $id = $this->data['peopleGroup1']->getId();
        $this->client->request('GET', "/group/$id/person/new");

        $this->client->submitForm('send', [
            'person_role_person[person][firstname]' => 'Larissa',
            'person_role_person[person][lastname]' => 'MULLER',
            'person_role_person[person][birthdate]' => '1987-05-09',
            'person_role_person[person][gender]' => Person::GENDER_FEMALE,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testAddSamePersonInGroupIsFailed()
    {
        $this->createLogin($this->user);

        $id = $this->data['peopleGroup1']->getId();
        $this->client->request('GET', "/group/$id/person/new");

        $this->client->submitForm('send', [
            'person_role_person[person][firstname]' => 'John',
            'person_role_person[person][lastname]' => 'DOE',
            'person_role_person[person][birthdate]' => '1980-01-01',
            'person_role_person[person][gender]' => Person::GENDER_MALE,
            'person_role_person[role]' => 5,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testAddPersonInGroupIsSuccessful()
    {
        $this->createLogin($this->user);

        $id = $this->data['peopleGroup1']->getId();
        $this->client->request('GET', "/group/$id/person/new");

        $this->client->submitForm('send', [
            'person_role_person[person][firstname]' => 'Larissa',
            'person_role_person[person][lastname]' => 'MULLER',
            'person_role_person[person][birthdate]' => '1987-05-09',
            'person_role_person[person][gender]' => Person::GENDER_FEMALE,
            'person_role_person[role]' => 5,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupe');
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditPersonInGroupIsUp()
    {
        $this->createLogin($this->user);

        $id = $this->data['peopleGroup1']->getId();
        $personId = $this->person->getId();
        $slug = $this->person->getSlug();
        $this->client->request('GET', "/group/$id/person/$personId-$slug");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->person->getFullname());
    }

    public function testShowPersonIsUp()
    {
        $this->createLogin($this->user);

        $id = $this->data['person1']->getId();
        $this->client->request('GET', "/person/$id");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->person->getFullname());
    }

    public function testShowPersonWithEdition()
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
        ]);

        $this->createLogin($this->user);

        $id = $this->data['person1']->getId();
        $this->client->request('GET', "/person/$id");

        $this->assertSelectorExists('#updatePerson');
    }

    public function testShowPersonWithoutEdition()
    {
        $this->createLogin($this->data['user5']);

        $id = $this->data['person1']->getId();
        $this->client->request('GET', "/person/$id");

        $this->assertSelectorNotExists('#updatePerson');
    }

    public function testEditPersonIsFailed()
    {
        $this->createLogin($this->user);

        $id = $this->data['person1']->getId();
        $this->client->request('POST', "/person/$id/edit", [
            'person' => [
                'firstname' => 'Johnny',
                'lastname' => 'DOE',
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);
    }

    public function testEditPersonIsSuccessful()
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
        ]);

        $this->createLogin($this->user);

        $id = $this->data['person1']->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/person/$id");
        $csrfToken = $crawler->filter('#person__token')->attr('value');

        $this->client->request('POST', "/person/$id/edit", [
            'person' => [
                'firstname' => 'Johnny',
                'lastname' => 'DOE',
                'birthdate' => '1980-10-01',
                'gender' => Person::GENDER_MALE,
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Les modifications sont enregistrées.', $content['msg']);
    }

    public function testAddNewGroupToPersonIsSuccessful()
    {
        $this->createLogin($this->user);

        $id = $this->data['person1']->getId();
        $this->client->request('POST', "/person/$id/new_group");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->person->getFullname());
        $this->assertSelectorExists('.alert.alert-danger');

        /** @var Crawler */
        $crawler = $this->client->request('GET', "/person/$id");
        $csrfToken = $crawler->filter('#person_new_group__token')->attr('value');

        $this->client->request('POST', "/person/$id/new_group", [
            'person_new_group' => [
                'peopleGroup' => [
                    'familyTypology' => 2,
                    'nbPeople' => 1,
                ],
                'role' => 5,
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Le nouveau groupe est créé.');
    }

    public function testSearchPersonWithResults()
    {
        $this->createLogin($this->user);

        // 0 result
        $this->client->request('GET', '/search/person/XXX');

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(0, count($content['people']));

        // 1 result
        $this->client->request('GET', '/search/person/John Doe');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(1, count($content['people']));

        // 2 results
        $this->client->request('GET', '/search/person/Doe');

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertGreaterThanOrEqual(2, count($content['people']));
    }

    public function testDeletePersonWithRoleUser()
    {
        $this->createLogin($this->user);

        $id = $this->data['person1']->getId();
        $this->client->request('GET', "/person/$id/delete");

        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testDeletePersonWithRoleAdmin()
    {
        $this->createLogin($this->userAdmin);

        $id = $this->data['person1']->getId();
        $this->client->request('GET', "/person/$id/delete");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'La personne est supprimée.');
    }

    public function testDuplicatePeoplePageIsUp()
    {
        $this->createLogin($this->userAdmin);

        $this->client->request('GET', '/duplicated_people');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Doublons | Personnes');
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
