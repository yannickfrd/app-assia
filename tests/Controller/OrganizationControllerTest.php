<?php

namespace App\Tests\Controller;

use App\Entity\Organization;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class OrganizationControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var Organization */
    protected $organization;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/OrganizationFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->organization = $this->dataFixtures['organization1'];
    }

    public function testListOrganizationsIsUp()
    {
        $this->client->request('GET', $this->generateUri('organizations'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Organismes prescripteurs');
    }

    public function testNewOrganizationIsUp()
    {
        $this->client->request('GET', $this->generateUri('admin_organization_new'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouvel organisme');
    }

    public function testCreateNewOrganizationIsSuccessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('admin_organization_new'));

        $faker = \Faker\Factory::create('fr_FR');

        $form = $crawler->selectButton('send')->form([
            'organization[name]' => 'Organisme test',
            'organization[comment]' => $faker->paragraphs(6, true),
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditOrganizationisUp()
    {
        $this->client->request('GET', $this->generateUri('admin_organization_edit', [
            'id' => $this->organization->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->organization->getName());
    }

    public function testEditOrganizationIsSuccessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('admin_organization_edit', [
            'id' => $this->organization->getId(),
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
