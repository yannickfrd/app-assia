<?php

namespace App\Tests\Controller;

use App\Entity\PeopleGroup;
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
    protected $dataFixtures;

    /** @var PeopleGroup */
    protected $peopleGroup;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/PersonFixturesTest.yaml',
        ]);

        $this->peopleGroup = $this->dataFixtures['peopleGroup1'];
    }

    public function testListPeopleGroupsIsUp()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->client->request('GET', $this->generateUri('people_groups'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupes de personnes');
    }

    public function testSearchPeopleGroupsIsSucessful()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('people_groups'));

        $form = $crawler->selectButton('search')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupes de personnes');
    }

    public function testEditPeopleGroupIsUp()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->client->request('GET', $this->generateUri('people_group_show', [
            'id' => $this->peopleGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupe');
    }

    public function testEditPeopleGroupIsSuccessful()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('people_group_show', [
            'id' => $this->peopleGroup->getId(),
        ]));

        $faker = \Faker\Factory::create('fr_FR');

        $form = $crawler->selectButton('send')->form([
            'group[familyTypology]' => 1,
            'group[nbPeople]' => 1,
            'group[comment]' => $faker->paragraphs(6, true),
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupe');
    }

    public function testDeletePeopleGroupWithRoleUser()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->client->request('GET', $this->generateUri('people_group_delete', [
            'id' => $this->peopleGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testDeletePeopleGroupWithRoleAdmin()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        $this->client->request('GET', $this->generateUri('people_group_delete', [
            'id' => $this->peopleGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testTryAddPersonInGroupIsUp()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->client->request('POST', $this->generateUri('group_add_person', [
            'id' => $this->peopleGroup->getId(),
            'person_id' => $this->dataFixtures['person1']->getId(),
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-danger', "Une erreur s'est produite.");
    }

    public function testFailToRemovePersonInGroup()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->client->request('GET', $this->generateUri('role_person_remove', [
            'id' => $this->dataFixtures['rolePerson']->getId(),
            '_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('remove'.$this->dataFixtures['rolePerson']->getId()),
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('danger', $data['alert']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
