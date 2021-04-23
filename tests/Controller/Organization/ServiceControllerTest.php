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

    public function testSearchServicesIsSuccessful()
    {
        $data = $this->loadFixtureFiles($this->getFixtureFiles());
        $this->createLogin($data['userRoleUser']);

        $this->client->request('GET', '/services');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Services');

        $this->client->submitForm('search', [
            'name' => 'CHRS XXX',
            'city' => 'Pontoise',
            'phone' => '01 00 00 00 00',
            'pole' => $data['pole1']->getId(),
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Services');
    }

    public function testExportServicesIsSuccessful()
    {
        $data = $this->loadFixtureFiles($this->getFixtureFiles());
        $this->createLogin($data['userRoleUser']);

        $this->client->request('GET', '/services');

        $this->client->submitForm('export', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('.spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreateNewServiceIsSuccessful()
    {
        $data = $this->loadFixtureFiles($this->getFixtureFiles());
        $this->createLogin($data['userSuperAdmin']);

        $this->client->request('GET', '/service/new');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau service');

        $this->client->submitForm('send', [
            'service[name]' => 'Service test',
            'service[location][city]' => 'Pontoise',
            'service[phone1]' => '01 00 00 00 00',
            'service[pole]' => $data['pole1'],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditServiceInSuperAdminIsUp()
    {
        $data = $this->loadFixtureFiles(array_merge($this->getFixtureFiles(), [
            dirname(__DIR__).'/../DataFixturesTest/PlaceFixturesTest.yaml',
        ]));

        $this->createLogin($data['userSuperAdmin']);

        $service = $data['service1'];
        $id = $service->getId();
        $this->client->request('GET', "/service/$id");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $service->getName());
    }

    public function testEditServiceIsSuccessful()
    {
        $data = $this->loadFixtureFiles(array_merge($this->getFixtureFiles(), [
            dirname(__DIR__).'/../DataFixturesTest/PlaceFixturesTest.yaml',
        ]));

        $this->createLogin($data['userAdmin']);

        $service = $data['service1'];
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
        $data = $this->loadFixtureFiles($this->getFixtureFiles());

        $this->createLogin($data['userSuperAdmin']);

        $id = $data['service1']->getId();
        $this->client->request('GET', "/admin/service/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'est désactivé');

        $this->client->request('GET', "/admin/service/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-success', 'est ré-activé');
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
        $data = null;
    }
}
