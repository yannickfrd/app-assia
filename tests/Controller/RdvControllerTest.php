<?php

namespace App\Tests\Controller;

use App\Entity\Rdv;
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
    protected $dataFixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Rdv */
    protected $rdv;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/RdvFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->supportGroup = $this->dataFixtures['supportGroup'];
        $this->rdv = $this->dataFixtures['rdv1'];
    }

    public function testViewListRdvsIsUp()
    {
        $this->client->request('GET', $this->generateUri('rdvs'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Rendez-vous');
    }

    public function testSearchRdvsIsSuccessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('rdvs'));

        $form = $crawler->selectButton('search')->form([
            'title' => 'RDV 666',
            'date[start]' => '2020-01-01',
            'date[end]' => (new \DateTime())->format('Y-m-d'),
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Rendez-vous');
    }

    public function testViewSupportListRdvsIsUp()
    {
        $this->client->request('GET', $this->generateUri('support_rdvs', [
            'id' => $this->supportGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Rendez-vous');
    }

    public function testShowCalendarIsUp()
    {
        $this->client->request('GET', $this->generateUri('calendar'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h2', 'Agenda');
    }

    public function testShowSupportCalendarIsUp()
    {
        $this->client->request('GET', $this->generateUri('support_calendar', [
            'id' => $this->supportGroup->getId(),
        ]));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h2', 'Agenda');
    }

    public function testShowDayIsUp()
    {
        $this->client->request('GET', $this->generateUri('calendar_day_show', [
            'year' => '2020',
            'month' => '4',
            'day' => '2',
        ]));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h3', 'Jour');
    }

    public function testFailToCreateNewRdvInSupport()
    {
        $this->client->request('POST', $this->generateUri('support_rdv_new', [
            'id' => $this->supportGroup->getId(),
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('danger', $data['alert']);
    }

    public function testFailToCreateNewRdv()
    {
        $this->client->request('POST', $this->generateUri('rdv_new'));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('danger', $data['alert']);
    }

    public function testGetRdv()
    {
        $this->client->request('GET', $this->generateUri('rdv_get', [
            'id' => $this->rdv->getId(),
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('show', $data['action']);
    }

    public function testFailToEditRdv()
    {
        $this->client->request('POST', $this->generateUri('rdv_edit', [
            'id' => $this->rdv->getId(),
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('danger', $data['alert']);
    }

    public function testDeleteRdv()
    {
        $this->client->request('GET', $this->generateUri('rdv_delete', [
            'id' => $this->rdv->getId(),
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('delete', $data['action']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
