<?php

namespace App\Tests\Controller\Organization;

use App\Tests\AppTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class ServiceControllerTest extends WebTestCase
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

    public function testSearchServicesIsSuccessful()
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->createLogin($fixtures['userRoleUser']);

        $this->client->request('GET', '/services');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Services');

        $this->client->submitForm('search', [
            'name' => 'CHRS Cergy',
            'city' => 'Pontoise',
            'phone' => '01 00 00 00 00',
            'pole' => $fixtures['pole1']->getId(),
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Services');
    }

    public function testExportServicesIsSuccessful()
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->createLogin($fixtures['userRoleUser']);

        $this->client->request('GET', '/services');

        $this->client->submitForm('export', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('.spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreateNewServiceIsSuccessful()
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->createLogin($fixtures['userSuperAdmin']);

        $this->client->request('GET', '/service/new');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau service');

        $this->client->submitForm('send', [
            'service[name]' => 'Service test',
            'service[location][city]' => 'Pontoise',
            'service[phone1]' => '01 00 00 00 00',
            'service[pole]' => $fixtures['pole1'],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditServiceInSuperAdminIsUp()
    {
        $fixtures = $this->databaseTool->loadAliceFixture(array_merge($this->getFixtureFiles(), [
            dirname(__DIR__).'/../DataFixturesTest/PlaceFixturesTest.yaml',
        ]));

        $this->createLogin($fixtures['userSuperAdmin']);

        $service = $fixtures['service1'];
        $id = $service->getId();
        $this->client->request('GET', "/service/$id");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $service->getName());
    }

    public function testEditServiceIsSuccessful()
    {
        $fixtures = $this->databaseTool->loadAliceFixture(array_merge($this->getFixtureFiles(), [
            dirname(__DIR__).'/../DataFixturesTest/PlaceFixturesTest.yaml',
        ]));

        $this->createLogin($fixtures['userAdmin']);

        $service = $fixtures['service1'];
        $id = $service->getId();
        $this->client->request('GET', "/service/$id");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $service->getName());

        $this->client->submitForm('send', [
            'service[name]' => 'Service test edit',
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testDisableService()
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());

        $this->createLogin($fixtures['userSuperAdmin']);

        $id = $fixtures['service1']->getId();
        $this->client->request('GET', "/admin/service/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'est désactivé');

        $this->client->request('GET', "/admin/service/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'est ré-activé');
    }

    protected function getFixtureFiles(): array
    {
        return [
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $fixtures = null;
    }
}
