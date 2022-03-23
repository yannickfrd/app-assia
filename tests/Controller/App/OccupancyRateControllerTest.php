<?php

namespace App\Tests\Controller\App;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OccupancyRateControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

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
    }

    public function testPageOccupancyByDeviceIsUp(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/occupancy/devices');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Dispositifs');
    }

    public function testPageOccupancyByServiceIsUp(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/occupancy/services');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Services');
    }

    public function testPageOccupancyBySubServiceIsUp(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['service1']->getId();
        $this->client->request('GET', "/occupancy/service/$id/sub_services");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'CHRS Cergy');
    }

    public function testPageOccupancyByPlaceIsUp(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/occupancy/places');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Groupes de places');
    }

    public function testPageOccupancySubServicesByPlaceIsUp(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $subService = $this->fixtures['sub_service1'];
        $id = $subService->getId();
        $this->client->request('GET', "/occupancy/sub_services/$id/places");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'CHRS sous-service');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
