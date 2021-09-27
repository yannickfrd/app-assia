<?php

namespace App\Tests\Controller;

use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
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
    protected $data;

    /** @var Service */
    protected $service;

    /** @var Device */
    protected $device;

    protected function setUp(): void
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/DeviceFixturesTest.yaml',
        ]);

        $this->service = $this->data['service1'];
        $this->device = $this->data['device1'];
    }

    public function testListDevicesIsUp()
    {
        $this->createLogin($this->data['userAdmin']);

        $this->client->request('GET', '/admin/devices');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Dispositifs');
    }

    public function testNewDeviceIsUp()
    {
        $this->createLogin($this->data['userAdmin']);

        $this->client->request('GET', '/admin/device/new');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau dispositif');
    }

    public function testCreateDeviceIsFailed()
    {
        $this->createLogin($this->data['userAdmin']);

        $this->client->request('GET', '/admin/device/new');

        // Test without name
        $this->client->submitForm('send', [
            'device[name]' => '',
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('input#device_name.is-invalid');

        // Test with device exists already
        $this->client->submitForm('send', [
            'device[name]' => 'Insertion',
            ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('span.form-error-message', 'Ce dispositif existe déjà.');
    }

    public function testCreateDeviceIsSuccessful()
    {
        $this->createLogin($this->data['userAdmin']);

        $this->client->request('GET', '/admin/device/new');

        $this->client->submitForm('send', [
            'device[name]' => 'Nouveau dispositif',
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditDeviceIsSuccessful()
    {
        $this->createLogin($this->data['userAdmin']);

        $id = $this->device->getId();
        $this->client->request('GET', "/admin/device/$id");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->device->getName());

        $this->client->submitForm('send');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testDisableDeviceIsFailed()
    {
        $this->createLogin($this->data['userAdmin']);

        $id = $this->device->getId();
        $this->client->request('GET', "/admin/device/$id/disable");

        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testDisableDeviceIsSuccessful()
    {
        $this->createLogin($this->data['userSuperAdmin']);

        $id = $this->device->getId();
        $this->client->request('GET', "/admin/device/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'Le dispositif est désactivé.');

        $this->client->request('GET', "/admin/device/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'Le dispositif est ré-activé.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->data = null;
    }
}
