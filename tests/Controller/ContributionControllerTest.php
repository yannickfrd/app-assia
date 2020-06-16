<?php

namespace App\Tests\Controller;

use App\Entity\Contribution;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class ContributionControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Contribution */
    protected $contribution;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/ContributionFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->supportGroup = $this->dataFixtures['supportGroup'];
        $this->contribution = $this->dataFixtures['contribution1'];
    }

    public function testViewListContributionsIsUp()
    {
        $this->client->request('GET', $this->generateUri('contributions'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Paiements');
    }

    public function testSearchContributionsIsSuccessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('contributions'));

        $form = $crawler->selectButton('search')->form([
            'date[start][year]' => '2020',
            'date[start][month]' => '04',
            'date[start][day]' => '01',
            'date[end][year]' => '2020',
            'date[end][month]' => '04',
            'date[end][day]' => '30',
            ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Paiements');
    }

    public function testExportContributions()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('contributions'));

        $form = $crawler->selectButton('export')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testViewSupportListContributionsIsUp()
    {
        $this->client->request('GET', $this->generateUri('support_contributions', [
            'id' => $this->supportGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Paiements');
    }

    public function testGetResources()
    {
        $this->client->request('GET', $this->generateUri('support_resources', [
            'id' => $this->supportGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    // public function testNewContribution()
    // {
    //     $this->client->request('GET', $this->generateUri('contribution_new', [
    //         'id' => $this->supportGroup->getId(),
    //     ]));

    //     $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    // }

    public function testGetContribution()
    {
        $this->client->request('GET', $this->generateUri('contribution_get', [
            'id' => $this->contribution->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    // public function testEditContribution()
    // {
    //     $this->client->request('GET', $this->generateUri('contribution_edit', [
    //         'id' => $this->contribution->getId(),
    //     ]));

    //     $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    // }

    public function testDeleteContribution()
    {
        $this->client->request('GET', $this->generateUri('contribution_delete', [
            'id' => $this->contribution->getId(),
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
