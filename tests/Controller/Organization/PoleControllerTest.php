<?php

namespace App\Tests\Controller\Organization;

use App\Entity\Organization\Pole;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class PoleControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var Pole */
    protected $pole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
        ]);

        $this->client->loginUser($this->fixtures['user_super_admin']);

        $this->pole = $this->fixtures['pole1'];
    }

    public function testListPolesIsUp(): void
    {
        $this->client->request('GET', '/poles');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Pôles');
    }

    public function testCreateNewPoleIsSuccessful(): void
    {
        $this->client->request('GET', '/pole/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Nouveau pôle');

        $this->client->submitForm('send', [
            'pole[name]' => 'Pôle test',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditPoleIsSuccessful(): void
    {
        $id = $this->pole->getId();
        $this->client->request('GET', "/pole/$id");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->pole->getName());

        $this->client->submitForm('send');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $this->pole->getName());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
