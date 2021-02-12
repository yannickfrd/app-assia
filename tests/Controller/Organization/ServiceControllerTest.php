<?php

namespace App\Tests\Controller;

use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ServiceControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    protected function setUp()
    {
    }

    public function testListServicesIsUp()
    {
        $dataFixtures = $this->loadFixtureFiles($this->getFixtureFiles());
        $this->createLogin($dataFixtures['userRoleUser']);

        $this->client->request('GET', $this->generateUri('services'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Services');
    }

    public function testSearchServicesIsSuccessful()
    {
        $dataFixtures = $this->loadFixtureFiles($this->getFixtureFiles());
        $this->createLogin($dataFixtures['userRoleUser']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('services'));

        $form = $crawler->selectButton('search')->form([
            'name' => 'AVDL',
            'city' => 'Pontoise',
            'phone' => '01 00 00 00 00',
            'pole' => 1,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Services');
    }

    public function testNewServiceIsUp()
    {
        $dataFixtures = $this->loadFixtureFiles($this->getFixtureFiles());
        $this->createLogin($dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('service_new'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau service');
    }

    public function testCreateNewServiceIsSuccessful()
    {
        $dataFixtures = $this->loadFixtureFiles($this->getFixtureFiles());
        $this->createLogin($dataFixtures['userSuperAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('service_new'));

        $form = $crawler->selectButton('send')->form([
            'service[name]' => 'Service test',
            'service[location][city]' => 'Pontoise',
            'service[phone1]' => '01 00 00 00 00',
            'service[pole]' => 1,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditServiceisUpInSuperAdmin()
    {
        $dataFixtures = $this->loadFixtureFiles(array_merge($this->getFixtureFiles(), [
            dirname(__DIR__).'/../DataFixturesTest/PlaceFixturesTest.yaml',
        ]));

        $service = $dataFixtures['service1'];

        $this->createLogin($dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('service_edit', [
            'id' => $service->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $service->getName());
    }

    public function testEditServiceisUpInRoleAdmin()
    {
        $dataFixtures = $this->loadFixtureFiles(array_merge($this->getFixtureFiles(), [
            dirname(__DIR__).'/../DataFixturesTest/PlaceFixturesTest.yaml',
        ]));

        $service = $dataFixtures['service1'];

        $this->createLogin($dataFixtures['userRoleAdmin']);

        $this->client->request('GET', $this->generateUri('service_edit', [
            'id' => $service->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $service->getName());
    }

    public function testEditServiceisSuccessful()
    {
        $dataFixtures = $this->loadFixtureFiles(array_merge($this->getFixtureFiles(), [
            dirname(__DIR__).'/../DataFixturesTest/PlaceFixturesTest.yaml',
        ]));

        $this->createLogin($dataFixtures['userRoleAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('service_edit', [
            'id' => $dataFixtures['service1']->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testDisableService()
    {
        $dataFixtures = $this->loadFixtureFiles($this->getFixtureFiles());

        $this->createLogin($dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('service_disable', [
            'id' => $dataFixtures['service1']->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-warning');
    }

    protected function getFixtureFiles()
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
        $dataFixtures = null;
    }
}
