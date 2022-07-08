<?php

namespace App\Tests\Controller\Organization;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceSettingTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();
    
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    /**
     * @dataProvider provideUser
     */
    public function testShowServiceSettingByRole(string $user): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->client->loginUser($fixtures[$user]);

        $service = $fixtures['service1'];
        $id = $service->getId();

        $this->client->request('GET', "/service/$id");

        $this->assertResponseIsSuccessful();

        if ('john_user' !== $user) {
            $this->assertSelectorExists('section#accordion_item_settings');
            $this->assertSelectorTextContains('html', 'Paramètres');
        } else {
            $this->assertSelectorNotExists('section#accordion_item_settings');
            $this->assertSelectorTextNotContains('html', 'Paramètres');
        }
    }

    public function provideUser(): \Generator
    {
        yield ['user_super_admin'];
        yield ['john_user'];
        yield ['user_admin'];
    }

    protected function getFixtureFiles(): array
    {
        return [
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
        ];
    }

}