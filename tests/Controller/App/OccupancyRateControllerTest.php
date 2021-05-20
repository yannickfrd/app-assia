<?php


namespace App\Tests\Controller\App;

use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OccupancyRateControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $data;

    protected function setUp()
    {
        $this->data = $this->loadFixtureFiles([
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
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/occupancy/devices');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Dispositifs');
    }

    public function testPageOccupancyByServiceIsUp()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/occupancy/services');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Services');
    }

    public function testPageOccupancyBySubServiceIsUp()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->data['service1']->getId();
        $this->client->request('GET', "/occupancy/service/$id/sub_services");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'CHRS XXX');
    }

    public function testPageOccupancyByPlaceIsUp()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/occupancy/places');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupes de places');
    }

    public function testPageOccupancySubServicesByPlaceIsUp()
    {
        $this->createLogin($this->data['userRoleUser']);

        $subService = $this->data['subService1'];
        $id = $subService->getId();
        $this->client->request('GET', "/occupancy/sub_services/$id/places");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'CHRS sous-service');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->data = null;
    }
}
