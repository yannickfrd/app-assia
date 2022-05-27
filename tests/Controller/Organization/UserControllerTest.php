<?php

namespace App\Tests\Controller\Organization;

use App\Entity\Organization\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class UserControllerTest extends WebTestCase
{
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

        $this->client = static::createClient();
        $this->client->followRedirects();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
        ]);

        $this->user = $this->fixtures['john_user'];
    }

    public function testSearchUsersPageIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/directory/users');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Utilisateurs');

        $this->client->submitForm('search', [
            'lastname' => 'SUPER_ADMIN',
            'firstname' => 'Role',
            'status' => 6,
            'phone' => '01 00 00 00 00',
            'service[services]' => [$this->fixtures['service1']],
        ], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table tbody tr td:nth-child(2)', 'SUPER_ADMIN');
    }

    public function testExportUsersIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/directory/users');

        $this->client->submitForm('export', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testAdminListUsersIsUp(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $this->client->request('GET', '/admin/users');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Administration des utilisateurs');

        $this->client->submitForm('export', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testUsernameExistsIsTrue(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/user/username_exists/user_super_admin');

        $result = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($result['response']);
    }

    public function testUsernameExistsIsFalse(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/user/username_exists/xxx');

        $result = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotTrue($result['response']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
