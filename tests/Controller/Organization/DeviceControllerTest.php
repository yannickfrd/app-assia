<?php

namespace App\Tests\Controller\Organization;

use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DeviceControllerTest extends WebTestCase
{
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

        $this->client = static::createClient();
        $this->client->followRedirects();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/device_fixtures_test.yaml',
        ]);

        $this->service = $this->fixtures['service1'];
        $this->device = $this->fixtures['device1'];
    }

    public function testListDevicesIsUp(): void
    {
        $this->client->loginUser($this->fixtures['user_admin']);

        $this->client->request('GET', '/admin/devices');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Dispositifs');
    }

    public function testNewDeviceIsUp(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $this->client->request('GET', '/admin/device/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Nouveau dispositif');
    }

    public function testCreateDeviceIsFailed(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $this->client->request('GET', '/admin/device/new');

        // Test without name
        $this->client->submitForm('send', [
            'device[name]' => '',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input#device_name.is-invalid');

        // Test with device exists already
        $this->client->submitForm('send', [
            'device[name]' => 'Insertion',
            ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('span.form-error-message', 'Ce dispositif existe déjà.');
    }

    public function testCreateDeviceIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $this->client->request('GET', '/admin/device/new');

        $this->client->submitForm('send', [
            'device[name]' => 'Nouveau dispositif',
            'device[code]' => 22,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditDeviceIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $id = $this->device->getId();
        $this->client->request('GET', "/admin/device/$id");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->device->getName());

        $this->client->submitForm('send');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testDisableDeviceIsFailed(): void
    {
        $this->client->loginUser($this->fixtures['user_admin']);

        $id = $this->device->getId();
        $this->client->request('GET', "/admin/device/$id/disable");

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDisableDeviceIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $id = $this->device->getId();
        $this->client->request('GET', "/admin/device/$id/disable");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-warning', 'Le dispositif est désactivé.');

        $this->client->request('GET', "/admin/device/$id/disable");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-success', 'Le dispositif est ré-activé.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
