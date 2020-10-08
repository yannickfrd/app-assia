<?php

namespace App\Tests\Controller;

use App\Entity\Device;
use App\Entity\Service;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DeviceControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var Service */
    protected $service;

    /** @var Device */
    protected $device;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/DeviceFixturesTest.yaml',
        ]);

        $this->service = $this->dataFixtures['service'];
        $this->device = $this->dataFixtures['device1'];
    }

    public function testListDevicesIsUp()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        $this->client->request('GET', $this->generateUri('admin_devices'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Dispositifs');
    }

    public function testNewDeviceIsUp()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        $this->client->request('GET', $this->generateUri('admin_device_new'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau dispositif');
    }

    public function testFailToCreateDevice()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('admin_device_new'));

        $form = $crawler->selectButton('send')->form([
            'device[name]' => '',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testCreateDeviceThatExists()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('admin_device_new', [
            'id' => $this->service->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([
            'device[name]' => 'AVDL',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testSuccessToCreateNewDevice()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('admin_device_new', [
            'id' => $this->service->getId(),
            'device[coefficient]' => mt_rand(1, 10),
        ]));

        $form = $crawler->selectButton('send')->form([
            'device[name]' => 'Nouveau dispositif',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditDeviceisUp()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        $this->client->request('GET', $this->generateUri('admin_device_edit', [
            'id' => $this->device->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->device->getName());
    }

    public function testSuccessToEditDevice()
    {
        $this->createLogin($this->dataFixtures['userRoleAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('admin_device_edit', [
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
