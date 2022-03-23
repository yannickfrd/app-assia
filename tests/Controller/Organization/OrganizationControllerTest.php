<?php

namespace App\Tests\Controller\Organization;

use App\Entity\Organization\Organization;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class OrganizationControllerTest extends WebTestCase
{
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

        $this->client = static::createClient();
        $this->client->followRedirects();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/organization_fixtures_test.yaml',
        ]);

        $this->client->loginUser($this->fixtures['user_admin']);

        $this->organization = $this->fixtures['organization1'];
    }

    public function testListOrganizationsIsUp(): void
    {
        $this->client->request('GET', '/organizations');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Organismes');
    }

    public function testSortOrganizationsIsSuccessful(): void
    {
        /** @var Crawler $crawler */
        $crawler = $this->client->request('GET', '/organizations');

        $link = $crawler->filter('table thead tr a.sortable')->first()->link();

        $this->client->click($link);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Organismes');
    }

    public function testCreateNewOrganizationIsSuccessful(): void
    {
        $this->client->request('GET', '/admin/organization/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Nouvel organisme');

        $this->client->submitForm('send', [
            'organization[name]' => 'Organisme test',
            'organization[comment]' => 'XXX',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditOrganizationIsSuccessful(): void
    {
        $id = $this->organization->getId();
        $this->client->request('GET', "/admin/organization/ $id");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->organization->getName());

        $this->client->submitForm('send');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
