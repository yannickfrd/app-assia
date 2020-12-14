<?php

namespace App\Tests\Controller;

use App\Entity\Organization\Service;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DatabaseBackupControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var Service */
    protected $service;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userSuperAdmin']);
    }

    public function testBackupDatabasePageIsUp()
    {
        $this->client->request('GET', $this->generateUri('database_backups'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Sauvegarde et export base de données');
    }

    // public function testCreateBackup()
    // {
    //     $this->client->request('GET', $this->generateUri('database_backup_create'));

    //     $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    //     $this->assertSelectorExists('.alert.alert-success');
    // }

    // public function testGetDatabaseBackup()
    // {
    //     $this->client->request('GET', $this->generateUri('database_backup_create'));

    //     $this->client->request('GET', $this->generateUri('database_backup_get', [
    //         'id' => 1,
    //     ]));

    //     $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    // }

    // public function testDeleteDatabase()
    // {
    //     $this->client->request('GET', $this->generateUri('database_backup_create'));

    //     $this->client->request('GET', $this->generateUri('database_backup_delete', [
    //         'id' => 1,
    //     ]));

    //     $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    //     $this->assertSelectorExists('.alert.alert-warning');
    // }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
