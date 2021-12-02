<?php

namespace App\Tests\Controller\Organization;

use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DeviceControllerTest extends WebTestCase
{
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var Service */
    protected $service;

    /** @var Device */
    protected $device;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();

        /* @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/DeviceFixturesTest.yaml',
        ]);

        $this->service = $this->fixtures['service1'];
        $this->device = $this->fixtures['device1'];
    }

    public function testListDevicesIsUp()
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $this->client->request('GET', '/admin/devices');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Dispositifs');
    }

    public function testNewDeviceIsUp()
    {
        $this->createLogin($this->fixtures['userSuperAdmin']);

        $this->client->request('GET', '/admin/device/new');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau dispositif');
    }

    public function testCreateDeviceIsFailed()
    {
        $this->createLogin($this->fixtures['userSuperAdmin']);

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
        $this->createLogin($this->fixtures['userSuperAdmin']);

        $this->client->request('GET', '/admin/device/new');

        $this->client->submitForm('send', [
            'device[name]' => 'Nouveau dispositif',
            'device[code]' => 22,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditDeviceIsSuccessful()
    {
        $this->createLogin($this->fixtures['userSuperAdmin']);

        $id = $this->device->getId();
        $this->client->request('GET', "/admin/device/$id");

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', $this->device->getName());

        $this->client->submitForm('send');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testDisableDeviceIsFailed()
    {
        $this->createLogin($this->fixtures['userAdmin']);

        $id = $this->device->getId();
        $this->client->request('GET', "/admin/device/$id/disable");

        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testDisableDeviceIsSuccessful()
    {
        $this->createLogin($this->fixtures['userSuperAdmin']);

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
        $this->fixtures = null;
    }
}
