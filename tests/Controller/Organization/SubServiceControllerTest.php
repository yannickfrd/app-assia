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

    public function testNewSubServiceIsUp()
    {
        $dataFixtures = $this->loadFixtureFiles($this->getFixtureFiles());
        $this->createLogin($dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('sub_service_new', [
            'id' => $dataFixtures['service1']->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau sous-service');
    }

    public function testCreateNewSubServiceIsSuccessfull()
    {
        $dataFixtures = $this->loadFixtureFiles($this->getFixtureFiles());
        $this->createLogin($dataFixtures['userRoleAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('sub_service_new', [
            'id' => $dataFixtures['service1']->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([
            'sub_service[name]' => 'Sous-service test',
            'sub_service[phone1]' => '01 00 00 00 00',
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditSubServiceisUp()
    {
        $dataFixtures = $this->loadFixtureFiles($this->getFixtureFiles());
        $this->createLogin($dataFixtures['userRoleAdmin']);

        $this->client->request('GET', $this->generateUri('sub_service_edit', [
            'id' => $dataFixtures['subService1']->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $dataFixtures['subService1']->getName());
    }

    public function testEditSubServiceisSuccessful()
    {
        $dataFixtures = $this->loadFixtureFiles($this->getFixtureFiles());
        $this->createLogin($dataFixtures['userRoleAdmin']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('sub_service_edit', [
            'id' => $dataFixtures['subService1']->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testDisableSubService()
    {
        $dataFixtures = $this->loadFixtureFiles($this->getFixtureFiles());
        $this->createLogin($dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('sub_service_disable', [
            'id' => $dataFixtures['subService1']->getId(),
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
