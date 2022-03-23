<?php

namespace App\Tests\Controller\Rdv;

use App\Entity\Event\Rdv;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class RdvControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $fixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Rdv */
    protected $rdv;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        /** @var AbstractDatabaseTool */
        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/rdv_fixtures_test.yaml',
        ]);

        $this->supportGroup = $this->fixtures['support_group1'];
        $this->rdv = $this->fixtures['rdv1'];
    }

    public function testSearchRdvsIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);
        $this->client->request('GET', '/rdvs');

        // Page is up
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Rendez-vous');

        // Search is successful
        $this->client->submitForm('search', [
            'title' => 'RDV test',
            'date[start]' => '2020-01-01',
            'date[end]' => (new \DateTime())->format('Y-m-d'),
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Rendez-vous');
    }

    public function testExportRdvsIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);
        $this->client->request('GET', '/rdvs');

        // Export with no result
        $this->client->submitForm('export', [
            'date[start]' => (new \Datetime())->modify('+10 year')->format('Y-m-d'),
        ], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-warning', 'Aucun résultat à exporter');

        // Export with results
        $this->client->submitForm('export', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testShowSupportListRdvsIsUp(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);
        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/rdvs");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Rendez-vous');
    }

    public function testCreateNewRdvIsSuccessful(): void
    {
        /** @var User */
        $user = $this->fixtures['john_user'];
        $this->client->loginUser($user);

        /** @var Crawler */
        $crawler = $this->client->request('GET', '/calendar/month');
        $csrfToken = $crawler->filter('#rdv__token')->attr('value');
        $now = new \DateTime();

        // Fail
        $this->client->request('POST', '/rdv/create');

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);

        // Success
        $this->client->request('POST', '/rdv/create', [
            'rdv' => [
                'title' => 'RDV test',
                'users' => [$user->getId()],
                'start' => $now->format('Y-m-d\TH:00'),
                'end' => (clone $now)->modify('+1 hour')->format('Y-m-d\TH:00'),
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('create', $content['action']);
        $this->assertSame('RDV test', $content['rdv']['title']);
    }

    public function testCreateNewSupportRdvIsSuccessful(): void
    {        
        /** @var User */
        $user = $this->fixtures['john_user'];
        $this->client->loginUser($user);

        $id = $this->supportGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/calendar/month");
        $csrfToken = $crawler->filter('#rdv__token')->attr('value');
        $now = new \DateTime();

        // Fail
        $this->client->request('POST', "/support/$id/rdv/create");

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);

        // Success
        $this->client->request('POST', "/support/$id/rdv/create", [
            'rdv' => [
                'title' => 'RDV test',
                'users' => [$user->getId()],
                'start' => $now->format('Y-m-d\TH:00'),
                'end' => (clone $now)->modify('+1 hour')->format('Y-m-d\TH:00'),
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('create', $content['action']);
        $this->assertSame('RDV test', $content['rdv']['title']);
    }

    public function testGetRdv(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->rdv->getId();
        $this->client->request('GET', "rdv/$id/show");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('show', $content['action']);
    }

    public function testEditRdvIsSuccessful(): void
    {
        /** @var User */
        $user = $this->fixtures['john_user'];

        $this->client->loginUser($user);

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
                'users' => [$user->getId()],
                'start' => $now->format('Y-m-d\TH:00'),
                'end' => (clone $now)->modify('+1 hour')->format('Y-m-d\TH:00'),
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('edit', $content['action']);
        $this->assertSame('RDV test edit', $content['rdv']['title']);
    }

    public function testEditRdvWithOtherUserIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user4']);

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

    public function testDeleteRdv(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);
        $id = $this->rdv->getId();
        $this->client->request('GET', "/rdv/$id/delete");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('delete', $content['action']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
