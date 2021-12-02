<?php

namespace App\Tests\Controller\Organization;

use App\Tests\AppTestTrait;
use App\Entity\Organization\Organization;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class OrganizationControllerTest extends WebTestCase
{
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var Organization */
    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/OrganizationFixturesTest.yaml',
        ]);

        $this->createLogin($this->fixtures['userAdmin']);

        $this->organization = $this->fixtures['organization1'];
    }

    public function testListOrganizationsIsUp()
    {
        $this->client->request('GET', '/organizations');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Organismes');
    }

    public function testSortOrganizationsIsSuccessful()
    {
        /** @var Crawler $crawler */
        $crawler = $this->client->request('GET', '/organizations');

        $link = $crawler->filter('table thead tr a.sortable')->first()->link();

        $this->client->click($link);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Organismes');
    }

    public function testCreateNewOrganizationIsSuccessful()
    {
        $this->client->request('GET', '/admin/organization/new');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouvel organisme');

        $this->client->submitForm('send', [
            'organization[name]' => 'Organisme test',
            'organization[comment]' => 'XXX',
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditOrganizationIsSuccessful()
    {
        $id = $this->organization->getId();
        $this->client->request('GET', "/admin/organization/ $id");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $this->organization->getName());

        $this->client->submitForm('send');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        $this->client = null;
        $this->fixtures = null;
    }
}
