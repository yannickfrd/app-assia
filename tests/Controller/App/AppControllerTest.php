<?php

namespace App\Tests\Controller\App;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppControllerTest extends WebTestCase
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

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    protected function loadFixtures(): void
    {
        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
        ]);
    }

    public function testLoginPageIsUp(): void
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Merci de vous connecter');
    }

    /**
     * @dataProvider getUrls
     */
    public function testPageIsUp(string $url, string $selector, string $text): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains($selector, $text);
    }

    /**
     * @dataProvider getUsers
     */
    public function testHomePageIsUp(string $user): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures[$user]);

        $this->client->request('GET', '/home');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tableau de bord');
    }

    public function testPageServiceDashboardWithRoleAdmin(): void
    {
        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
        ]);

        $this->client->loginUser($this->fixtures['user_admin']);

        $this->client->request('GET', '/dashboard/supports_by_user');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Répartition des suivis en cours');

        $this->client->submitForm('search', [
            'service[services]' => ['services' => $this->fixtures['service2']],
            'send' => true,
        ], 'GET');

        $this->assertResponseIsSuccessful();
    }

    public function getUsers(): ?\Generator
    {
        yield ['john_user'];
        yield ['user_admin'];
        yield ['user_super_admin'];
    }

    public function getUrls(): ?\Generator
    {
        yield ['/login', 'h1', 'Tableau de bord'];
        yield ['/managing', 'h1', 'Gestion'];
        yield ['/dashboard/supports_by_user', 'h1', 'Répartition des suivis en cours'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
