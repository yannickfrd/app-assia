<?php

namespace App\Tests\Controller\People;

use App\Service\Grammar;
use App\Entity\People\PeopleGroup;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class PeopleGroupControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;
    
    /** @var array */
    protected $fixtures;

    /** @var PeopleGroup */
    protected $peopleGroup;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = static::createClient();
        $this->client->followRedirects();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
        ]);

        $this->peopleGroup = $this->fixtures['people_group1'];
    }

    public function testEditPeopleGroupIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->peopleGroup->getId();
        $this->client->request('GET', "/group/$id");

        // Page is up
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Groupe');

        // Edit is successful
        $this->client->submitForm('send', [
            'group[familyTypology]' => 1,
            'group[nbPeople]' => 1,
            'group[comment]' => 'XXX',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Groupe');
    }

    public function testDeletePeopleGroupWithRoleUser(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->peopleGroup->getId();
        $this->client->request('GET', "/group/$id/delete");

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeletePeopleGroupWithRoleAdmin(): void
    {
        $this->client->loginUser($this->fixtures['user_admin']);

        $id = $this->peopleGroup->getId();
        $this->client->request('GET', "/group/$id/delete");

        $this->assertResponseIsSuccessful();
    }

    public function testAddPersonToGroupIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->peopleGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/group/$id/search_person");
        $csrfToken = $crawler->filter('#role_person__token')->attr('value');

        // Fail
        $personId = $this->fixtures['person1']->getId();
        $this->client->request('POST', "/group/$id/add_person/$personId");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-danger', 'Une erreur s\'est produite.');

        // Success
        $person = $this->fixtures['person5'];
        $personId = $person->getId();
        $this->client->request('POST', "/group/$id/add_person/$personId", [
            'role_person' => [
                'role' => 1,
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-success', $person->getFullname().' est ajouté'.Grammar::gender($person->getGender()).' au groupe.');
    }

    public function testRemovePersonInGroupIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_admin']);

        $id = $this->peopleGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/group/$id");
        $url = $crawler->filter('button[data-action="remove"]')->last()->attr('data-url');

        // Fail
        $id = $this->fixtures['role_person2']->getId();
        $this->client->request('GET', "/role_person/$id/remove/token");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-danger');

        // Success
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-warning', 'est retiré');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
