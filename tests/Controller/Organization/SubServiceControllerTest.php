<?php

namespace App\Tests\Controller\Organization;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SubServiceControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        /* @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testCreateNewSubServiceIsSuccessful(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->client->loginUser($fixtures['user_admin']);

        $id = $fixtures['service2']->getId();
        $this->client->request('GET', "/service/$id/sub-service/new");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Nouveau sous-service');

        $this->client->submitForm('send', [
            'sub_service[name]' => 'Sous-service test',
            'sub_service[phone1]' => '01 00 00 00 00',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.toast.alert-success');
    }

    public function testEditSubServiceIsSuccessful(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture(array_merge($this->getFixtureFiles(), [
            dirname(__DIR__).'/../fixtures/place_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
        ]));

        $this->client->loginUser($fixtures['user_super_admin']);

        $id = $fixtures['sub_service1']->getId();
        $this->client->request('GET', "/sub-service/$id");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $fixtures['sub_service1']->getName());

        $this->client->submitForm('send', []);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.toast.alert-success');
    }

    public function testDisableSubService(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->client->loginUser($fixtures['user_super_admin']);

        $id = $fixtures['sub_service1']->getId();
        $this->client->request('GET', "/sub-service/$id/disable");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-warning', 'a été désactivé');

        $this->client->request('GET', "/sub-service/$id/disable");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.toast.alert-success', 'a été réactivé');
    }

    protected function getFixtureFiles(): array
    {
        return [
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $fixtures = null;
    }
}
