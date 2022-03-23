<?php

namespace App\Tests\Controller;

use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceTagControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $fixtures;

    /** @var Service */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        /** @var AbstractDatabaseTool */
        $databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/tag_fixtures_test.yaml',
        ]);

        $this->service = $this->fixtures['service1'];
    }

    public function testServicePageWithTagsIsUp(): void
    {
        /** @var User $admin */
        $admin = $this->fixtures['user_admin'];
        $this->client->loginUser($admin);
        $service = $admin->getServiceUser()->first()->getService();

        $crawler = $this->client->request('GET', '/service/'.$service->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="service_tag"]');
        $this->assertCount(5, $crawler->filter('select#service_tag_tags option'));
    }

    public function testGetFormServiceTagsWidthBadRoleUserIsFailed(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/service/'.$this->fixtures['service1']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('form[name="service_tag"]');
    }

    public function testAddTagIsSuccessful(): void
    {
        /** @var User $admin */
        $admin = $this->fixtures['user_admin'];
        $this->client->loginUser($admin);
        $service = $admin->getServiceUser()->first()->getService();

        $this->client->request('GET', '/service/'.$service->getId());
        $this->client->submitForm('add_tags', [
            'service_tag[tags]' => ['2', '3'],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertEquals('success', json_decode($this->client->getResponse()->getContent())->alert);
    }

    public function testRemoveTagIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_admin']);

        $crawler = $this->client->request('GET', '/service/'.$this->service->getId());

        $this->assertSelectorExists($tagSelector = "span.badge[data-tag-id='{$this->fixtures['tag1']->getId()}']");

        $link = $crawler->filter("$tagSelector a")->link();
        $this->client->click($link);

        $this->assertSelectorNotExists($tagSelector);
    }

    public function testDeleteTagServiceTagIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_admin']);

        $this->client->request('GET', '/service/'.$this->fixtures['service2'].'/delete-tag/'.$this->fixtures['tag1']);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertSame('delete', $response['action']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
