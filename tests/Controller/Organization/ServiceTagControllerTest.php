<?php

namespace App\Tests\Controller;

use App\Entity\Organization\Service;
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

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/TagFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
        ]);

        $this->service = $this->fixtures['service1'];
    }

    public function testServicePageWithTagsIsUp()
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $crawler = $this->client->request('GET', '/service/'.$this->service->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name="service_tag"]');
        self::assertCount(5, $crawler->filter('select#service_tag_tags option'));
    }

    public function testGetFormServiceTagsWidthBadRoleUserIsFailed()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/service/'.$this->service->getId());

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('form[name="service_tag"]');
    }

    public function testAddTagIsSuccessful()
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $this->client->request('GET', '/service/'.$this->service->getId());
        $this->client->submitForm('add_tags', [
            'service_tag[tags]' => ['2', '3'],
        ]);

        self::assertResponseIsSuccessful();
        self::assertEquals('success', json_decode($this->client->getResponse()->getContent())->alert);
    }

    public function testRemoveTagIsSuccessful()
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $crawler = $this->client->request('GET', '/service/'.$this->service->getId());

        self::assertSelectorExists($tagSelector = "span.badge[data-tag-id='{$this->fixtures['tag1']->getId()}']");

        $link = $crawler->filter("$tagSelector a")->link();
        $this->client->click($link);

        self::assertSelectorNotExists($tagSelector);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->fixtures = null;
    }
}
