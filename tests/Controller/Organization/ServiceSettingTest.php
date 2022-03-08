<?php

namespace App\Tests\Controller\Organization;

use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceSettingTest extends WebTestCase
{
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    /**
     * @dataProvider provideUser
     */
    public function testShowServiceSettingByRole(string $user): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->createLogin($fixtures[$user]);

        $service = $fixtures['service1'];
        $id = $service->getId();

        $this->client->request('GET', "/service/$id");

        $this->assertResponseIsSuccessful();

        if ('userRoleUser' !== $user) {
            $this->assertSelectorExists('section#accordion_parent_settings');
            $this->assertSelectorTextContains('html', 'Paramètres');
        } else {
            $this->assertSelectorNotExists('section#accordion_parent_settings');
            $this->assertSelectorTextNotContains('html', 'Paramètres');
        }
    }

    public function provideUser(): \Generator
    {
        yield ['userSuperAdmin'];
        yield ['userRoleUser'];
        yield ['userAdmin'];
    }

    protected function getFixtureFiles(): array
    {
        return [
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
        ];
    }

}