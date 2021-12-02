<?php

namespace App\Tests\Controller\Organization;

use App\Entity\Organization\Referent;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ReferentControllerTest extends WebTestCase
{
    use AppTestTrait;

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

        $this->client = $this->createClient();

        /* @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    protected function getFixtureFiles()
    {
        return [
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ReferentFixturesTest.yaml',
        ];
    }

    public function testCreateReferentByPeopleGroupIsSuccessful()
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());

        $this->createLogin($fixtures['userRoleUser']);

        $id = $fixtures['peopleGroup1']->getId();
        $this->client->request('GET', "/group/$id/referent/new");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Nouveau service social référent');

        $this->client->submitForm('send', [
            'referent' => [
                'name' => 'Référent test',
                'type' => 1,
                'socialWorker' => 'XXXX',
                'socialWorker2' => 'XXXX',
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditReferentIsSuccessful()
    {
        $fixtures = $this->databaseTool->loadAliceFixture(($this->getFixtureFiles()));

        $this->createLogin($fixtures['userRoleUser']);

        $id = $fixtures['referent1']->getId();
        $this->client->request('GET', "/referent/$id/edit");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', $fixtures['referent1']->getName());

        $this->client->submitForm('send', [
            'referent[name]' => 'Référent test edit',
            'referent[type]' => 2,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Référent test edit');
    }

    public function testDeleteReferentIsSuccessful()
    {
        $fixtures = $this->databaseTool->loadAliceFixture(($this->getFixtureFiles()));

        $this->createLogin($fixtures['userRoleUser']);

        $id = $fixtures['referent1']->getId();
        $this->client->request('GET', "/referent/$id/delete");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Group');
    }

    public function testCreateReferentBySupportIsSuccessful()
    {
        $fixtures = $this->databaseTool->loadAliceFixture((array_merge($this->getFixtureFiles(), [
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
        ])));

        $this->createLogin($fixtures['userRoleUser']);

        $id = $fixtures['supportGroup1']->getId();
        $this->client->request('GET', "/support/$id/referent/new");

        $this->client->submitForm('send', [
            'referent[name]' => 'Référent test',
            'referent[type]' => 1,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.alert-success');

        $this->client->clickLink('Supprimer');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'Le service social Référent test est supprimé.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $fixtures = null;
    }
}
