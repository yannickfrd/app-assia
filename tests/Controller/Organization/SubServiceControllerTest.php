<?php

namespace App\Tests\Controller;

use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SubServiceControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    protected function setUp()
    {
    }

    public function testCreateNewSubServiceIsSuccessful()
    {
        $data = $this->loadFixtureFiles($this->getFixtureFiles());
        $this->createLogin($data['userAdmin']);

        $id = $data['service1']->getId();
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
        $data = $this->loadFixtureFiles(array_merge($this->getFixtureFiles(), [
            dirname(__DIR__).'/../DataFixturesTest/PlaceFixturesTest.yaml',
        ]));

        $this->createLogin($data['userAdmin']);

        $id = $data['subService1']->getId();
        $this->client->request('GET', "/sub-service/$id");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $data['subService1']->getName());

        $this->client->submitForm('send', []);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testDisableSubService()
    {
        $data = $this->loadFixtureFiles($this->getFixtureFiles());
        $this->createLogin($data['userSuperAdmin']);

        $id = $data['subService1']->getId();
        $this->client->request('GET', "/sub-service/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'est désactivé');

        $this->client->request('GET', "/sub-service/$id/disable");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success', 'est ré-activé');
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
