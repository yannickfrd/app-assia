<?php

namespace App\Tests\Controller\App;

use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AppControllerTest extends WebTestCase
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

    public function testAccessHomePageInRoleSuperAdmin()
    {
        $this->client = static::createClient();
        $this->client->followRedirects(true);

        $user = $this->data['userSuperAdmin'];

        $this->client->request('POST', '/login', [
            '_username' => $user->getUsername(),
            '_password' => $user->getPlainPassword(),
            '_csrf_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate'),
        ]);

        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testAccessHomePageInRoleAdmin()
    {
        $this->createLogin($this->data['userAdmin']);

        $this->client->request('GET', '/home');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Tableau de bord');
    }

    public function testAccessHomePageInRoleUser()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/home');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Tableau de bord');
    }

    public function testAccessToLoginPage()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/login');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Tableau de bord');
    }

    public function testPageServiceDashboardInRoleUser()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/dashboard/supports_by_user');

        $this->assertSelectorTextContains('h1', 'Répartition des suivis en cours');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testPageServiceDashboardInRoleAdmin()
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
        ]);

        $this->createLogin($this->data['userAdmin']);

        $this->client->request('GET', '/dashboard/supports_by_user');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Répartition des suivis en cours');

        $this->client->submitForm('search', [
            'service' => [
                'services' => $this->data['service1'],
            ],
            'send' => true,
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testPageAdminIsUp()
    {
        $this->createLogin($this->data['userAdmin']);

        $this->client->request('GET', '/admin');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Administration');
    }

    public function testPageManagingIsUp()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/managing');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Gestion');
    }

    public function testCacheClearIsSuccessful()
    {
        $this->createLogin($this->data['userSuperAdmin']);

        $this->client->request('GET', '/admin/cache/clear');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Le cache est vidé.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->data = null;
    }
}
