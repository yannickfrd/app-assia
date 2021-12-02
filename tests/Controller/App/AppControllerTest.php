<?php

namespace App\Tests\Controller\App;

use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AppControllerTest extends WebTestCase
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

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    protected function loadFixtures(): void
    {
        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
        ]);
    }

    public function testHomepageIsUp()
    {
        $this->loadFixtures();

        $this->client->followRedirects(true);

        $this->client->request('GET', '/');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Merci de vous connecter');
    }

    public function testAccessHomePageInRoleSuperAdmin()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userSuperAdmin']);

        $this->client->request('GET', '/home');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Tableau de bord');
    }

    public function testAccessHomePageInRoleAdmin()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userAdmin']);

        $this->client->request('GET', '/home');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Tableau de bord');
    }

    public function testAccessHomePageInRoleUser()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/home');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Tableau de bord');
    }

    public function testAccessToLoginPage()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/login');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Tableau de bord');
    }

    public function testPageServiceDashboardInRoleUser()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/dashboard/supports_by_user');

        $this->assertSelectorTextContains('h1', 'Répartition des suivis en cours');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testPageServiceDashboardInRoleAdmin()
    {
        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
        ]);

        $this->createLogin($this->fixtures['userAdmin']);

        $this->client->request('GET', '/dashboard/supports_by_user');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Répartition des suivis en cours');

        $this->client->submitForm('search', [
            'service' => [
                'services' => $this->fixtures['service1'],
            ],
            'send' => true,
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testPageAdminIsUp()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userAdmin']);

        $this->client->request('GET', '/admin');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Administration');
    }

    public function testPageManagingIsUp()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/managing');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Gestion');
    }

    public function testCacheClearIsSuccessful()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userSuperAdmin']);

        $this->client->request('GET', '/admin/cache/clear');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Le cache est vidé.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $this->fixtures = null;
    }
}
