<?php

namespace App\Tests\Controller\Organization;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReferentControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var Referent */
    protected $referent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        /* @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    protected function getFixtureFiles(): array
    {
        return [
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/referent_fixtures_test.yaml',
        ];
    }

    public function testCreateReferentByPeopleGroupIsSuccessful(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());

        $this->client->loginUser($fixtures['john_user']);

        $id = $fixtures['people_group1']->getId();
        $this->client->request('GET', "/group/$id/referent/new");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Service social référent');

        $this->client->submitForm('send', [
            'referent' => [
                'name' => 'Référent test',
                'type' => 1,
                'socialWorker' => 'XXXX',
                'socialWorker2' => 'XXXX',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.toast.alert-success');
    }

    public function testEditReferentIsSuccessful(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture(($this->getFixtureFiles()));

        $this->client->loginUser($fixtures['john_user']);

        $id = $fixtures['referent1']->getId();
        $this->client->request('GET', "/referent/$id/edit");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $fixtures['referent1']->getName());

        $this->client->submitForm('send', [
            'referent[name]' => 'Référent test edit',
            'referent[type]' => 2,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Référent test edit');
    }

    public function testDeleteReferentIsSuccessful(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture(($this->getFixtureFiles()));

        $this->client->loginUser($fixtures['john_user']);

        $id = $fixtures['referent1']->getId();
        $this->client->request('GET', "/referent/$id/delete");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Group');
    }

    public function testCreateReferentBySupportIsSuccessful(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture((array_merge($this->getFixtureFiles(), [
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
        ])));

        $this->client->loginUser($fixtures['john_user']);

        $id = $fixtures['support_group1']->getId();
        $this->client->request('GET', "/support/$id/referent/new");

        $this->client->submitForm('send', [
            'referent[name]' => 'Référent test',
            'referent[type]' => 1,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.toast.alert-success');

        $this->client->clickLink('Supprimer');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast.alert-warning', 'Le service social Référent test est supprimé.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $fixtures = null;
    }
}
