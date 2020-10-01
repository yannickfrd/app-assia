<?php

namespace App\Tests\Controller;

use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AppControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
        ]);
    }

    public function testHomepageIsUp()
    {
        $this->client = static::createClient();
        $this->client->followRedirects(true);

        $this->client->request('GET', '/');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Merci de vous connecter');
    }

    public function testAccessHomePage()
    {
        $this->client = static::createClient();
        $this->client->followRedirects(true);

        $this->client->request('POST', $this->generateUri('security_login'), [
            '_username' => 'r.madelaine',
            '_password' => 'Test123*',
            '_csrf_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate'),
        ]);

        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testPageServiceDashboardIsUp()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('supports_by_user'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Répartition des suivis en cours');
    }

    public function testPageOccupancyByDeviceIsUp()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('occupancy_devices'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Dispositifs');
    }

    public function testPageOccupancyByServiceIsUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
        ]);
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('occupancy_services'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Services');
    }

    public function testPageOccupancyByAccommodationsIsUp()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('occupancy_accommodations'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Groupes de places');
    }

    public function testPageAdminIsUp()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('admin'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Administration');
    }

    public function testPageManagingIsUp()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('managing'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Gestion');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
