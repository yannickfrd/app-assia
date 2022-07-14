<?php

namespace App\Tests\Controller\Organization;

use App\Entity\Organization\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class ServiceControllerTest extends WebTestCase
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

    public function testSearchServicesIsSuccessful(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->client->loginUser($fixtures['john_user']);

        $this->client->request('GET', '/services');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Services');

        $this->client->submitForm('search', [
            'name' => 'CHRS Cergy',
            'city' => 'Pontoise',
            'phone' => '01 00 00 00 00',
            'pole' => $fixtures['pole1']->getId(),
        ], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Services');
    }

    public function testExportServicesIsSuccessful(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->client->loginUser($fixtures['john_user']);

        $this->client->request('GET', '/services');

        $this->client->submitForm('export', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('.spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreateNewServiceIsSuccessful(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->client->loginUser($fixtures['user_super_admin']);

        $this->client->request('GET', '/service/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Nouveau service');

        $this->client->submitForm('send', [
            'service[name]' => 'Service test',
            'service[location][city]' => 'Pontoise',
            'service[phone1]' => '01 00 00 00 00',
            'service[pole]' => $fixtures['pole1'],
        ]);

        $this->assertResponseIsSuccessful('Le service est créé.');
    }

    public function testEditServiceInSuperAdminIsUp(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture(array_merge($this->getFixtureFiles(), [
            dirname(__DIR__).'/../fixtures/place_fixtures_test.yaml',
        ]));

        $this->client->loginUser($fixtures['user_super_admin']);

        $service = $fixtures['service1'];
        $id = $service->getId();
        $this->client->request('GET', "/service/$id");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $service->getName());
    }

    public function testEditServiceIsSuccessful(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture(array_merge($this->getFixtureFiles(), [
            dirname(__DIR__).'/../fixtures/place_fixtures_test.yaml',
        ]));

        /** @var User $admin */
        $admin = $fixtures['user_admin'];
        $this->client->loginUser($admin);

        $service = $admin->getServiceUser()->first()->getService();
        $id = $service->getId();
        $this->client->request('GET', "/service/$id");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $service->getName());

        $this->client->submitForm('send', [
            'service[name]' => 'Service test edit',
        ]);

        $this->assertResponseIsSuccessful('Les modifications sont enregistrées.');
    }

    public function testDisableService(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());

        $this->client->loginUser($fixtures['user_super_admin']);

        $id = $fixtures['service1']->getId();
        $this->client->request('GET', "/admin/service/$id/disable");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-warning', 'est désactivé');

        $this->client->request('GET', "/admin/service/$id/disable");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-success', 'est ré-activé');
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
