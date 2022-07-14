<?php

namespace App\Tests\Controller\Admin;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        /** @var AbstractDatabaseTool $databaseTool */
        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
        ]);
    }

    public function testPageAdminIsUp(): void
    {
        $this->client->loginUser($this->fixtures['user_admin']);

        $this->client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Administration');
    }

    public function testCacheClearIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $this->client->request('GET', '/admin/cache/clear');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-success', 'Le cache est vidé.');
    }

    public function testGlossaryPageIsUp(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $this->client->request('GET', '/admin/glossary');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Glossaire');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
