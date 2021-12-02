<?php

namespace App\Tests\Controller\Organization;

use App\Tests\AppTestTrait;
use App\Entity\Organization\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class UserControllerTest extends WebTestCase
{
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var User */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
        ]);

        $this->user = $this->fixtures['userRoleUser'];
    }

    public function testSearchUsersPageIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/directory/users');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Utilisateurs');

        $this->client->submitForm('search', [
            'lastname' => 'SUPER_ADMIN',
            'firstname' => 'Role',
            'status' => 6,
            'phone' => '01 00 00 00 00',
            'service[services]' => [$this->fixtures['service1']],
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('table tbody tr td:nth-child(2)', 'SUPER_ADMIN');
    }

    public function testExportUsersIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/directory/users');

        $this->client->submitForm('export', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testAdminListUsersIsUp()
    {
        $this->createLogin($this->fixtures['userSuperAdmin']);

        $this->client->request('GET', '/admin/users');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Administration des utilisateurs');

        $this->client->submitForm('export', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testUsernameExistsIsTrue()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/user/username_exists/r.super_admin');

        $result = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($result['response']);
    }

    public function testUsernameExistsIsFalse()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/user/username_exists/xxx');

        $result = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotTrue($result['response']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        $this->client = null;
        $this->fixtures = null;
    }
}
