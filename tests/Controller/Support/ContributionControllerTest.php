<?php

namespace App\Tests\Controller;

use App\Entity\Support\Contribution;
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
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ContributionFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->supportGroup = $this->dataFixtures['supportGroup'];
        $this->contribution = $this->dataFixtures['contribution1'];
    }

    public function test_view_list_contributions_is_up()
    {
        $this->client->request('GET', $this->generateUri('contributions'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Paiements');
    }

    public function test_search_contributions_is_successful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('contributions'));

        $form = $crawler->selectButton('search')->form([
            'date[start]' => '2020-04-01',
            'date[end]' => '2020-04-30',
            ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Paiements');
    }

    public function test_export_contributions()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('contributions'));

        $form = $crawler->selectButton('export')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function test_view_support_list_contributions_is_up()
    {
        $this->client->request('GET', $this->generateUri('support_contributions', [
            'id' => $this->supportGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Paiements');
    }

    public function test_get_resources()
    {
        $this->client->request('GET', $this->generateUri('support_resources', [
            'id' => $this->supportGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function test_export_support_contributions()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('support_contributions', [
            'id' => $this->supportGroup->getId(),
        ]));

        $form = $crawler->selectButton('export')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    // public function testNewContribution()
    // {
    //     $this->client->request('GET', $this->generateUri('contribution_new', [
    //         'id' => $this->supportGroup->getId(),
    //     ]));

    //     $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    // }

    public function test_get_contribution()
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

    public function test_delete_contribution()
    {
        $this->client->request('GET', $this->generateUri('contribution_delete', [
            'id' => $this->contribution->getId(),
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('delete', $data['action']);
    }

    public function test_contribution_export_pdf()
    {
        $this->client->request('GET', $this->generateUri('contribution_export_pdf', [
            'id' => $this->contribution->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
