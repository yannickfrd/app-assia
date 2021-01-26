<?php

namespace App\Tests\Controller;

use App\Entity\Organization\User;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var User */
    protected $user;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
        ]);

        $this->user = $this->dataFixtures['userRoleUser'];
    }

    public function test_list_users_page_is_up()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->client->request('GET', $this->generateUri('users'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Utilisateurs');
    }

    public function test_search_users_page_is_successful()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('users'));

        $form = $crawler->selectButton('search')->form([
            'lastname' => 'SUPER_ADMIN',
            'firstname' => 'Role',
            'status' => 6,
            'phone' => '01 00 00 00 00',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('table tbody tr td:nth-child(2)', 'SUPER_ADMIN');
    }

    public function test_export_users_is_successful()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('users'));

        $form = $crawler->selectButton('export')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function test_admin_list_users_is_up()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('admin_users'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Administration des utilisateurs');
    }

    public function test_username_exists_is_true()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->client->request('GET', '/user/username_exists/r.super_admin');

        $result = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($result['response']);
    }

    public function test_username_exists_is_false()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->client->request('GET', $this->generateUri('username_exists', [
            'value' => 'xxx',
        ]));

        $result = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotTrue($result['response']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
