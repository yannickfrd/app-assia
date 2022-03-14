<?php

namespace App\Tests\Controller\Rdv;

use App\Entity\Support\Rdv;
use App\Entity\Support\SupportGroup;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class RdvControllerTest extends WebTestCase
{
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Rdv */
    protected $rdv;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/RdvFixturesTest.yaml',
        ]);

        $this->supportGroup = $this->fixtures['supportGroup1'];
        $this->rdv = $this->fixtures['rdv1'];
    }

    public function testSearchRdvsIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);
        $this->client->request('GET', '/rdvs');

        // Page is up
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Rendez-vous');

        // Search is successful
        $this->client->submitForm('search', [
            'title' => 'RDV test',
            'date[start]' => '2020-01-01',
            'date[end]' => (new \DateTime())->format('Y-m-d'),
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Rendez-vous');
    }

    public function testExportRdvsIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);
        $this->client->request('GET', '/rdvs');

        // Export with no result
        $this->client->submitForm('export', [
            'date[start]' => (new \Datetime())->modify('+10 year')->format('Y-m-d'),
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert-warning', 'Aucun résultat à exporter');

        // Export with results
        $this->client->submitForm('export', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testShowSupportListRdvsIsUp()
    {
        $this->createLogin($this->fixtures['userRoleUser']);
        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/rdvs");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Rendez-vous');
    }

    public function testCreateNewRdvIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);
        /** @var Crawler */
        $crawler = $this->client->request('GET', '/calendar/month');
        $csrfToken = $crawler->filter('#rdv__token')->attr('value');
        $now = new \DateTime();

        // Fail
        $this->client->request('POST', '/rdv/new');

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);

        // Success
        $this->client->request('POST', '/rdv/new', [
            'rdv' => [
                'title' => 'RDV test',
                'start' => $now->format('Y-m-d\TH:00'),
                'end' => (clone $now)->modify('+1 hour')->format('Y-m-d\TH:00'),
                '_token' => $csrfToken,
            ],
        ]);

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('create', $content['action']);
        $this->assertSame('RDV test', $content['rdv']['title']);
    }

    public function testCreateNewSupportRdvIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);
        $id = $this->supportGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/calendar/month");
        $csrfToken = $crawler->filter('#rdv__token')->attr('value');
        $now = new \DateTime();

        // Fail
        $this->client->request('POST', "/support/$id/rdv/new");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);

        // Success
        $this->client->request('POST', "/support/$id/rdv/new", [
            'rdv' => [
                'title' => 'RDV test',
                'start' => $now->format('Y-m-d\TH:00'),
                'end' => (clone $now)->modify('+1 hour')->format('Y-m-d\TH:00'),
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('create', $content['action']);
        $this->assertSame('RDV test', $content['rdv']['title']);
    }

    public function testGetRdv()
    {
        $this->createLogin($this->fixtures['userRoleUser']);
        $id = $this->rdv->getId();
        $this->client->request('GET', "rdv/$id/show");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('show', $content['action']);
    }

    public function testEditRdvIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);
        /** @var Crawler */
        $crawler = $this->client->request('GET', '/calendar/month');
        $csrfToken = $crawler->filter('#rdv__token')->attr('value');
        $id = $this->rdv->getId();
        $now = new \DateTime();

        // Fail
        $this->client->request('POST', "/rdv/$id/edit");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);

        // Success
        $this->client->request('POST', "/rdv/$id/edit", [
            'rdv' => [
                'title' => 'RDV test edit',
                'start' => $now->format('Y-m-d\TH:00'),
                'end' => (clone $now)->modify('+1 hour')->format('Y-m-d\TH:00'),
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('update', $content['action']);
        $this->assertSame('RDV test edit', $content['rdv']['title']);
    }

    public function testEditRdvWithOtherUserIsSuccessful()
    {
        $this->createLogin($this->fixtures['user4']);

        $crawler = $this->client->request('GET', '/calendar/month');

        $now = new \DateTime();
        $this->client->request('POST', '/rdv/1/edit', [
            'rdv' => [
                'title' => 'RDV test edit',
                'start' => $now->format('Y-m-d\TH:00'),
                'end' => (clone $now)->modify('+1 hour')->format('Y-m-d\TH:00'),
                '_token' => $crawler->filter('#rdv__token')->attr('value'),
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('RDV test edit', $content['rdv']['title']);
    }

    public function testDeleteRdv()
    {
        $this->createLogin($this->fixtures['userRoleUser']);
        $id = $this->rdv->getId();
        $this->client->request('GET', "/rdv/$id/delete");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('delete', $content['action']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $this->fixtures = null;
    }
}
