<?php

namespace App\Tests\Controller\Organization;

use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SubServiceControllerTest extends WebTestCase
{
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();

        /* @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testCreateNewSubServiceIsSuccessful()
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->createLogin($fixtures['userAdmin']);

        $id = $fixtures['service2']->getId();
        $this->client->request('GET', "/service/$id/sub-service/new");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau sous-service');

        $this->client->submitForm('send', [
            'sub_service[name]' => 'Sous-service test',
            'sub_service[phone1]' => '01 00 00 00 00',
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditSubServiceIsSuccessful()
    {
        $fixtures = $this->databaseTool->loadAliceFixture(array_merge($this->getFixtureFiles(), [
            dirname(__DIR__).'/../DataFixturesTest/PlaceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
        ]));

        $this->createLogin($fixtures['userSuperAdmin']);

        $id = $fixtures['subService1']->getId();
        $this->client->request('GET', "/sub-service/$id");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $fixtures['subService1']->getName());

        $this->client->submitForm('send', []);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testDisableSubService()
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->createLogin($fixtures['userSuperAdmin']);

        $id = $fixtures['subService1']->getId();
        $this->client->request('GET', "/sub-service/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'est désactivé');

        $this->client->request('GET', "/sub-service/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success', 'est ré-activé');
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
