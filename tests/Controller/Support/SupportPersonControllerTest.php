<?php

namespace App\Tests\Controller\Support;

use App\Entity\Support\SupportGroup;
use App\Service\Grammar;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class SupportPersonControllerTest extends WebTestCase
{
    use AppTestTrait;

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

        $this->client = $this->createClient();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    protected function loadFixtures(): void
    {
        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
        ]);
    }

    public function testAddPersonToSupportIsSuccessful()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userSuperAdmin']);

        $person = $this->fixtures['person5'];
        $personId = $person->getId();
        $groupId = $this->fixtures['peopleGroup1']->getId();
        $supportId = $this->fixtures['supportGroup1']->getId();

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
            '.alert.alert-success',
            $person->getFullname().' est ajouté'.Grammar::gender($person->getGender()).' au suivi en cours.'
        );
    }

    public function testDeleteSupportPersonWithoutTokenIsFailed()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->fixtures['supportGroup1']->getId();
        $supportPersId = $this->fixtures['supportPerson2']->getId();
        $this->client->request('GET', "/support/$id/edit");
        $this->client->request('GET', "/support-person/$supportPersId/delete/tokenId");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-danger', 'Une erreur');
    }

    public function testDeleteSupportPersonIsSuccessful()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->fixtures['supportGroup1']->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/edit");
        $url = $crawler->filter('button[data-action="remove"]')->last()->attr('data-url');
        $this->client->request('GET', $url);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'est retiré');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $this->fixtures = null;

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();
    }
}
