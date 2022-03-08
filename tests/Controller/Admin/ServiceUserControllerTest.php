<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Organization\User;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ServiceUserControllerTest extends WebTestCase
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

        /* @var AbstractDatabaseTool */
        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
        ]);
    }

    public function testAccessIsSuccessful(): void
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $this->client->request('GET', '/admin/user/4');
        $this->assertResponseIsSuccessful();
    }

    public function testAccessBadUser(): void
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/admin/user/4');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAddUserService(): void
    {
        $this->createLogin($this->fixtures['userAdmin']);

        /** @var User $user */
        $user = $this->fixtures['userRoleUser'];
        $serviceId = $user->getServiceUser()->first()->getService()->getId();

        $crawler = $this->client->request('GET', '/admin/user/'.$user->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists("tr[data-service-id='$serviceId']");

        $this->client->request('POST', '/service-user/'.$user.'/add', [
            'userService' => [
                'serviceUser' => [
                    1 => ['service' => $serviceId],
                ],
                '_token' => $crawler->filter('#userServices__token')->attr('value'),
            ],
        ]);

        $this->assertSelectorExists("tr[data-service-id='$serviceId']");
    }

    public function testDeleteServiceUser(): void
    {
        $this->createLogin($this->fixtures['userAdmin']);

        /** @var User $user */
        $user = $this->fixtures['user5'];
        $serviceId = $user->getServiceUser()->first()->getService()->getId();

        $crawler = $this->client->request('GET', '/admin/user/'.$user->getId());

        $deletePath = $crawler->filter("tr[data-service-id='$serviceId'] button[data-action='delete']")->first()->attr('data-path');
        $this->client->jsonRequest('DELETE', $deletePath);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('delete', $response['action']);
        $this->assertStringContainsString('a été retiré.', $response['msg']);
    }

    public function testToggleMainService(): void
    {
        $this->createLogin($this->fixtures['userAdmin']);

        /** @var User $user */
        $user = $this->fixtures['user5'];

        $this->client->request('GET', '/admin/user/'.$user->getId());
        $this->assertResponseIsSuccessful();

        $this->client->jsonRequest('GET', '/service-user/'.$user->getServiceUser()->first()->getId().'/toggle-main', [
            'main' => true,
        ]);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('update', $response['action']);
        $this->assertStringContainsString('défini en service principal de '.$user->getFirstname(), $response['msg']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $this->fixtures = null;
    }
}
