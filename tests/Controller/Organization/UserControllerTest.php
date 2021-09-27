<?php

namespace App\Tests\Controller;

use App\Entity\Organization\User;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $data;

    /** @var User */
    protected $user;

    protected function setUp(): void
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
        ]);

        $this->user = $this->data['userRoleUser'];
    }

    public function testSearchUsersPageIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/directory/users');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Utilisateurs');

        $this->client->submitForm('search', [
            'lastname' => 'SUPER_ADMIN',
            'firstname' => 'Role',
            'status' => 6,
            'phone' => '01 00 00 00 00',
            'service[services]' => [$this->data['service1']],
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('table tbody tr td:nth-child(2)', 'SUPER_ADMIN');
    }

    public function testExportUsersIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/directory/users');

        $this->client->submitForm('export', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testAdminListUsersIsUp()
    {
        $this->createLogin($this->data['userSuperAdmin']);

        $this->client->request('GET', '/admin/users');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Administration des utilisateurs');

        $this->client->submitForm('export', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testUsernameExistsIsTrue()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/user/username_exists/r.super_admin');

        $result = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($result['response']);
    }

    public function testUsernameExistsIsFalse()
    {
        $this->createLogin($this->data['userRoleUser']);

        $this->client->request('GET', '/user/username_exists/xxx');

        $result = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotTrue($result['response']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->data = null;
    }
}
