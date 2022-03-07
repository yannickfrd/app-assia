<?php

namespace App\Tests\Controller;

use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceTagControllerTest extends WebTestCase
{
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var Service */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();

        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/TagFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
        ]);

        $this->service = $this->fixtures['service1'];
    }

    public function testServicePageWithTagsIsUp()
    {
        /** @var User $admin */
        $admin = $this->fixtures['userAdmin'];
        $this->createLogin($admin);
        $service = $admin->getServiceUser()->first()->getService();

        $crawler = $this->client->request('GET', '/service/'.$service->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="service_tag"]');
        $this->assertCount(5, $crawler->filter('select#service_tag_tags option'));
    }

    public function testGetFormServiceTagsWidthBadRoleUserIsFailed()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/service/'.$this->fixtures['service1']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('form[name="service_tag"]');
    }

    public function testAddTagIsSuccessful()
    {
        /** @var User $admin */
        $admin = $this->fixtures['userAdmin'];
        $this->createLogin($admin);
        $service = $admin->getServiceUser()->first()->getService();

        $this->client->request('GET', '/service/'.$service->getId());
        $this->client->submitForm('add_tags', [
            'service_tag[tags]' => ['2', '3'],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertEquals('success', json_decode($this->client->getResponse()->getContent())->alert);
    }

    public function testRemoveTagIsSuccessful()
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $crawler = $this->client->request('GET', '/service/'.$this->service->getId());

        $this->assertSelectorExists($tagSelector = "span.badge[data-tag-id='{$this->fixtures['tag1']->getId()}']");

        $link = $crawler->filter("$tagSelector a")->link();
        $this->client->click($link);

        $this->assertSelectorNotExists($tagSelector);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->fixtures = null;
    }
}
