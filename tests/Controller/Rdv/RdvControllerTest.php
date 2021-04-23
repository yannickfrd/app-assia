<?php

namespace App\Tests\Controller\Rdv;

use App\Entity\Support\Rdv;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class RdvControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $data;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Rdv */
    protected $rdv;

    protected function setUp()
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/RdvFixturesTest.yaml',
        ]);

        $this->createLogin($this->data['userRoleUser']);

        $this->supportGroup = $this->data['supportGroup1'];
        $this->rdv = $this->data['rdv1'];
    }

    public function testSearchRdvsIsSuccessful()
    {
        $this->client->request('GET', '/rdvs');

        // Page is up
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Rendez-vous');

        // Search is successful
        $this->client->submitForm('search', [
            'title' => 'RDV 666',
            'date[start]' => '2020-01-01',
            'date[end]' => (new \DateTime())->format('Y-m-d'),
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Rendez-vous');
    }

    public function testExportRdvsIsSuccessful()
    {
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
        $this->assertContains('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testViewSupportListRdvsIsUp()
    {
        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/rdvs");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Rendez-vous');
    }

    public function testCreateNewRdvIsSuccessful()
    {
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
                'title' => 'Rdv test',
                'start' => $now->format('Y-m-d\TH:00'),
                'end' => (clone $now)->modify('+1 hour')->format('Y-m-d\TH:00'),
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('create', $content['action']);
        $this->assertSame('Rdv test', $content['rdv']['title']);
    }

    public function testCreateNewSupportRdvIsSuccessful()
    {
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
                'title' => 'Rdv test',
                'start' => $now->format('Y-m-d\TH:00'),
                'end' => (clone $now)->modify('+1 hour')->format('Y-m-d\TH:00'),
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('create', $content['action']);
        $this->assertSame('Rdv test', $content['rdv']['title']);
    }

    public function testGetRdv()
    {
        $id = $this->rdv->getId();
        $this->client->request('GET', "rdv/$id/get");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('show', $content['action']);
    }

    public function testEditRdvIsSuccessful()
    {
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

    public function testDeleteRdv()
    {
        $id = $this->rdv->getId();
        $this->client->request('GET', "/rdv/$id/delete");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('delete', $content['action']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->data = null;
    }
}
