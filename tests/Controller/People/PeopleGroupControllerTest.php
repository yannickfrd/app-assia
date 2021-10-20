<?php

namespace App\Tests\Controller\People;

use App\Entity\People\PeopleGroup;
use App\Service\Grammar;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class PeopleGroupControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $data;

    /** @var PeopleGroup */
    protected $peopleGroup;

    protected function setUp(): void
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
        ]);

        $this->peopleGroup = $this->data['peopleGroup1'];
    }

    public function testEditPeopleGroupIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

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
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->peopleGroup->getId();
        $this->client->request('GET', "/group/$id/delete");

        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testDeletePeopleGroupWithRoleAdmin()
    {
        $this->createLogin($this->data['userAdmin']);

        $id = $this->peopleGroup->getId();
        $this->client->request('GET', "/group/$id/delete");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testAddPersonToGroupIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->peopleGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/group/$id/search_person");
        $csrfToken = $crawler->filter('#role_person__token')->attr('value');

        // Fail
        $personId = $this->data['person1']->getId();
        $this->client->request('POST', "/group/$id/add_person/$personId");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-danger', 'Une erreur s\'est produite.');

        // Success
        $person = $this->data['person5'];
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
        $this->createLogin($this->data['userAdmin']);

        $id = $this->peopleGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/group/$id");
        $url = $crawler->filter('button[data-action="remove"]')->last()->attr('data-url');

        // Fail
        $id = $this->data['rolePerson2']->getId();
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
        $this->data = null;
    }
}
