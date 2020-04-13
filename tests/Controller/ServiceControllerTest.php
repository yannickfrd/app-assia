<?php

namespace App\Tests\Controller;

use App\Entity\Service;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ServiceControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var Service */
    protected $service;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/ServiceFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->service = $this->dataFixtures['service1'];
    }

    public function testListServicesIsUp()
    {
        $this->client->request('GET', $this->generateUri('services'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Services');
    }

    public function testSearchServicesIsSuccessful()
    {
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
        $this->client->request('GET', $this->generateUri('service_new'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau service');
    }

    public function testCreateNewServiceIsSuccessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('service_new'));

        $form = $crawler->selectButton('send')->form([
            'service[name]' => 'Service test',
            'service[city]' => 'Pontoise',
            'service[phone1]' => '01 00 00 00 00',
            'service[pole]' => 1,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditServiceisUp()
    {
        $this->client->request('GET', $this->generateUri('service_edit', [
            'id' => $this->service->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->service->getName());
    }

    public function testEditServiceisSuccessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('service_edit', [
            'id' => $this->service->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
