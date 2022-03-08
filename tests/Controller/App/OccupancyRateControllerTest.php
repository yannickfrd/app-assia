<?php

namespace App\Tests\Controller\App;

use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OccupancyRateControllerTest extends WebTestCase
{
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PlaceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PlaceGroupFixturesTest.yaml',
        ]);
    }

    public function testPageOccupancyByDeviceIsUp()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/occupancy/devices');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Dispositifs');
    }

    public function testPageOccupancyByServiceIsUp()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/occupancy/services');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Services');
    }

    public function testPageOccupancyBySubServiceIsUp()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->fixtures['service1']->getId();
        $this->client->request('GET', "/occupancy/service/$id/sub_services");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'CHRS Cergy');
    }

    public function testPageOccupancyByPlaceIsUp()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/occupancy/places');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupes de places');
    }

    public function testPageOccupancySubServicesByPlaceIsUp()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $subService = $this->fixtures['subService1'];
        $id = $subService->getId();
        $this->client->request('GET', "/occupancy/sub_services/$id/places");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'CHRS sous-service');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $this->fixtures = null;
    }
}
