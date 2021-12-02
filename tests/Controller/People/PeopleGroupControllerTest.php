<?php

namespace App\Tests\Controller\People;

use App\Service\Grammar;
use App\Tests\AppTestTrait;
use App\Entity\People\PeopleGroup;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class PeopleGroupControllerTest extends WebTestCase
{
    use AppTestTrait;

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
        
        $this->client = $this->createClient();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
        ]);

        $this->peopleGroup = $this->fixtures['peopleGroup1'];
    }

    public function testEditPeopleGroupIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->peopleGroup->getId();
        $this->client->request('GET', "/group/$id");

        // Page is up
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupe');

        // Edit is successful
        $this->client->submitForm('send', [
            'group[familyTypology]' => 1,
            'group[nbPeople]' => 1,
            'group[comment]' => 'XXX',
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupe');
    }

    public function testDeletePeopleGroupWithRoleUser()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->peopleGroup->getId();
        $this->client->request('GET', "/group/$id/delete");

        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testDeletePeopleGroupWithRoleAdmin()
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $id = $this->peopleGroup->getId();
        $this->client->request('GET', "/group/$id/delete");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testAddPersonToGroupIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->peopleGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/group/$id/search_person");
        $csrfToken = $crawler->filter('#role_person__token')->attr('value');

        // Fail
        $personId = $this->fixtures['person1']->getId();
        $this->client->request('POST', "/group/$id/add_person/$personId");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
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

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', $person->getFullname().' est ajouté'.Grammar::gender($person->getGender()).' au groupe.');
    }

    public function testRemovePersonInGroupIsSuccessful()
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $id = $this->peopleGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/group/$id");
        $url = $crawler->filter('button[data-action="remove"]')->last()->attr('data-url');

        // Fail
        $id = $this->fixtures['rolePerson2']->getId();
        $this->client->request('GET', "/role_person/$id/remove/token");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');

        // Success
        $this->client->request('GET', $url);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'est retiré');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        $this->client = null;
        $this->fixtures = null;
    }
}
