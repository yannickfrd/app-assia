<?php

namespace App\Tests\Controller\Admin;

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
    protected $data;

    /** @var Service */
    protected $service;

    protected function setUp()
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
        ]);

        $this->createLogin($this->data['userSuperAdmin']);
    }

    public function testBackupDatabasePageIsUp()
    {
        $this->client->request('GET', '/admin/database-backups');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Sauvegarde et export base de données');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->data = null;
    }
}
