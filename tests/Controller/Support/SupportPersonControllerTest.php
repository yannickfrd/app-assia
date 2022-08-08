<?php

namespace App\Tests\Controller\Support;

use App\Entity\Support\SupportGroup;
use App\Service\Grammar;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;

class SupportPersonControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    protected function loadFixtures(): void
    {
        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
        ]);
    }

    public function testAddPersonToSupportIsSuccessful(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['user_super_admin']);

        $person = $this->fixtures['person5'];
        $personId = $person->getId();
        $groupId = $this->fixtures['people_group1']->getId();
        $supportId = $this->fixtures['support_group1']->getId();

        /** @var Crawler */
        $crawler = $this->client->request('GET', "/group/$groupId/search_person");

        $this->client->request('POST', "/group/$groupId/add_person/$personId", [
            'role_person' => [
                'role' => 1,
                'addPersonToSupport' => false,
                '_token' => $crawler->filter('#role_person__token')->attr('value'),
            ],
        ]);

        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$supportId/edit");

        $this->client->submitForm('add-person', [
            'add_person_to_support[rolePerson]' => $crawler->filter('#add_person_to_support_rolePerson option')->last()->attr('value'),
        ]);

        $this->assertSelectorTextContains(
            '.toast.alert-success',
            $person->getFullname().' a été ajouté'.Grammar::gender($person->getGender()).' au suivi'
        );
    }

    public function testDeleteSupportPersonWithoutTokenIsFailed(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['support_group1']->getId();
        $supportPersId = $this->fixtures['support_person2']->getId();
        $this->client->request('GET', "/support/$id/edit");
        $this->client->request('GET', "/support-person/$supportPersId/delete/tokenId");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-danger', 'Une erreur');
    }

    public function testDeleteSupportPersonIsSuccessful(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['support_group1']->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/edit");
        $url = $crawler->filter('button[data-action="remove"]')->last()->attr('data-path');
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-warning', 'a été retiré');
    }

    public function testGetPeopleInSupportGroupIsSuccessful(): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['support_group1']->getId();
        $this->client->request('GET', "/support/$id/people");

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertSame('get_support_people', $response['action']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();
    }
}
