<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Organization\Service;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DatabaseBackupControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var Service */
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
        ]);

        $this->client->loginUser($this->fixtures['user_super_admin']);
    }

    public function testBackupDatabasePageIsUp(): void
    {
        $this->client->request('GET', '/admin/database-backups');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Sauvegarde et export base de données');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
