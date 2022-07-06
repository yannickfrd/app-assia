<?php

namespace App\Tests\Controller\Support;

use App\Entity\Support\PlaceGroup;
use App\Entity\Support\SupportGroup;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Component\DomCrawler\Crawler;

class PlaceGroupControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;
    
    /** @var array */
    protected $fixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var PlaceGroup */
    protected $placeGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/place_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/place_group_fixtures_test.yaml',
        ]);

        $this->client->loginUser($this->fixtures['john_user']);

        $this->supportGroup = $this->fixtures['support_group1'];
        $this->placeGroup = $this->fixtures['pl_group1'];
    }

    public function testListSupportPlacesIsUp(): void
    {
        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/places");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');
    }

    public function testCreatePlaceGroupIsSuccessful(): void
    {
        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/place/new");

        // Page is up
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');

        // Page is up
        $this->client->submitForm('send', [
            'place_group[place]' => 1,
            'place_group[startDate]' => (new \DateTime())->format('Y-m-d'),
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('.alert.alert-success', 'L\'hébergement est créé.');
    }

    public function testCreateNewPlaceGroupIsFailed(): void
    {
        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/place/new");

        $this->client->submitForm('send', [
            'place_group[startDate]' => null,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testEditPlaceGroupIsSuccessful(): void
    {
        $id = $this->placeGroup->getId();
        $this->client->request('GET', "/support/place_group/$id");

        // Page is up
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');

        // Edit is successful
        $this->client->submitForm('send');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testAdPersonToPlaceIsSuccessful(): void
    {
        $placePersonId = $this->fixtures['pl_person1']->getId();
        $this->client->request('GET', "/support/place-person/$placePersonId/delete");

        $placeGroupId = $this->placeGroup->getId();
        /** @var Crawler $crawler */
        $crawler = $this->client->request('GET', "/support/place_group/$placeGroupId");

        $supportPersonId = $crawler->filter('form[name="add_person_to_place_group"] option')->last()->attr('value');

        $this->client->submitForm('add-person', [
            'add_person_to_place_group[supportPerson]' => $supportPersonId,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Logement/hébergement');
        $this->assertSelectorTextContains('.alert.alert-success', 'à la prise en charge.');
    }

    public function testDeletePlaceGroupIsSuccessful(): void
    {
        $id = $this->placeGroup->getId();
        $this->client->request('GET', "/support/group-people-place/$id/delete");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-warning', 'La prise en charge est supprimée.');
    }

    public function testDeletePlacePersonIsSuccessful(): void
    {
        $id = $this->fixtures['pl_person1']->getId();
        $this->client->request('GET', "/support/place-person/$id/delete");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-warning');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();
    }
}
