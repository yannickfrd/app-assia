<?php

namespace App\Tests\Controller\People;

use App\Entity\People\Person;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class PersonControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    protected $user_admin;

    /** @var Person */
    protected $person;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        /* @var AbstractDatabaseTool */
        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();
    }

    private function loadFixtures(): void
    {
        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
        ]);

        $this->user_admin = $this->fixtures['user_admin'];
        $this->user = $this->fixtures['john_user'];
        $this->person = $this->fixtures['person1'];
    }

    public function testPeoplePageIsUp(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

        $this->client->request('GET', '/people');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Rechercher une personne');
    }

    public function testSearchInPeoplePageIsSuccessful(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

        $this->client->request('POST', '/people/search', [
            'firstname' => 'John',
            'lastname' => 'DOE',
        ]);

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('DOE', $content['people'][0]['lastname']);
    }

    public function testAddPersonToGroupIsUp(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

        $id = $this->fixtures['people_group1']->getId();
        $this->client->request('GET', "/group/$id/search_person");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Ajouter une personne');
    }

    public function testNewPersonIsUp(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

        $this->client->request('GET', '/person/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Création d\'une personne');
    }

    public function testCreateNewPersonIsFailed(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

        $this->client->request('GET', '/person/new');

        $this->client->submitForm('send', [
            'role_person_group[person][firstname]' => 'Larissa',
            'role_person_group[person][lastname]' => 'MULLER',
            'role_person_group[person][birthdate]' => '1987-05-09',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testCreatePersonWhoExistsIsFailed(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

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

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-danger');
        $this->assertSelectorTextContains('div.invalid-feedback', 'Cette personne existe déjà !');
    }

    public function testNewPersonInGroupIsUp(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

        $id = $this->fixtures['people_group1']->getId();
        $this->client->request('GET', "/group/$id/person/new");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Création d\'une personne');
        $this->assertSelectorTextContains('div.container nav ol.breadcrumb li:last-child', 'Création d\'une personne');
    }

    public function testCreateNewPersonIsSuccessful(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

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

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Groupe');
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testAddPersonToGroupIsFailed(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

        $id = $this->fixtures['people_group1']->getId();
        $this->client->request('GET', "/group/$id/person/new");

        $this->client->submitForm('send', [
            'person_role_person[person][firstname]' => 'Larissa',
            'person_role_person[person][lastname]' => 'MULLER',
            'person_role_person[person][birthdate]' => '1987-05-09',
            'person_role_person[person][gender]' => Person::GENDER_FEMALE,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testAddSamePersonInGroupIsFailed(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

        $id = $this->fixtures['people_group1']->getId();
        $this->client->request('GET', "/group/$id/person/new");

        $this->client->submitForm('send', [
            'person_role_person[person][firstname]' => 'John',
            'person_role_person[person][lastname]' => 'DOE',
            'person_role_person[person][birthdate]' => '1980-01-01',
            'person_role_person[person][gender]' => Person::GENDER_MALE,
            'person_role_person[role]' => 5,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testAddPersonToGroupIsSuccessful(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

        $id = $this->fixtures['people_group1']->getId();
        $this->client->request('GET', "/group/$id/person/new");

        $this->client->submitForm('send', [
            'person_role_person[person][firstname]' => 'Larissa',
            'person_role_person[person][lastname]' => 'MULLER',
            'person_role_person[person][birthdate]' => '1987-05-09',
            'person_role_person[person][gender]' => Person::GENDER_FEMALE,
            'person_role_person[role]' => 5,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Groupe');
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditPersonInGroupIsUp(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

        $id = $this->fixtures['people_group1']->getId();
        $personId = $this->person->getId();
        $slug = $this->person->getSlug();
        $this->client->request('GET', "/group/$id/person/$personId-$slug");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->person->getFullname());
    }

    public function testShowPersonIsUp(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

        $id = $this->fixtures['person1']->getId();
        $this->client->request('GET', "/person/$id");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->person->getFullname());
    }

    public function testShowPersonWithEdition(): void
    {
        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
        ]);

        $this->user = $this->fixtures['john_user'];
        $this->client->loginUser($this->user);

        $id = $this->fixtures['person1']->getId();
        $this->client->request('GET', "/person/$id");

        $this->assertSelectorExists('#updatePerson');
    }

    public function testShowPersonWithoutEdition(): void
    {
        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
        ]);

        $this->client->loginUser($this->fixtures['user5']);

        $id = $this->fixtures['person1']->getId();
        $this->client->request('GET', "/person/$id");

        $this->assertSelectorNotExists('#updatePerson');
    }

    public function testEditPersonIsFailed(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

        $id = $this->fixtures['person1']->getId();
        $this->client->request('POST', "/person/$id/edit", [
            'person' => [
                'firstname' => 'Johnny',
                'lastname' => 'DOE',
                'gender' => Person::GENDER_MALE,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);
    }

    public function testEditPersonIsSuccessful(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
        ]);

        $this->client->loginUser($fixtures['john_user']);

        $id = $fixtures['person1']->getId();
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

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Les modifications sont enregistrées.', $content['msg']);
    }

    public function testAddNewGroupToPersonIsSuccessful(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

        $id = $this->fixtures['person1']->getId();
        $this->client->request('POST', "/person/$id/new_group");

        $this->assertResponseIsSuccessful();
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

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-success', 'Le nouveau groupe est créé.');
    }

    public function testSearchPersonWithResults(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

        // 0 result
        $this->client->request('GET', '/search/person/XXX');

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(0, count($content['people']));

        // 1 result
        $this->client->request('GET', '/search/person/John Doe');

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(1, count($content['people']));

        // 2 results
        $this->client->request('GET', '/search/person/Doe');

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertGreaterThanOrEqual(2, count($content['people']));
    }

    public function testDeletePersonWithRoleUser(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user);

        $id = $this->fixtures['person1']->getId();
        $this->client->request('GET', "/person/$id/delete");

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeletePersonWithRoleAdmin(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user_admin);

        $id = $this->fixtures['person1']->getId();
        $this->client->request('GET', "/person/$id/delete");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-warning', 'La personne est supprimée.');
    }

    public function testDuplicatePeoplePageIsUp(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->user_admin);

        $this->client->request('GET', '/duplicated_people');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Doublons | Personnes');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();
    }
}
