<?php

namespace App\Tests\Controller;

use App\Entity\Support\SupportGroup;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class IndicatorControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/EvaluationFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/NoteFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/RdvFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/DocumentFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ContributionFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userSuperAdmin']);
    }

    public function testPageIndicatorsIsUp()
    {
        $this->client->request('GET', $this->generateUri('daily_indicators'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Indicateurs quotidiens d\'activité');
    }

    public function testIndicatorServicesPageIsUp()
    {
        $this->client->request('GET', 'indicator/services');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Indicateurs d\'activité des services');
    }

    public function testSearchIndicatorServicesIsSuccessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', 'indicator/services');

        $now = new \DateTime();
        $form = $crawler->selectButton('search')->form([
            'status' => [SupportGroup::STATUS_IN_PROGRESS],
            'date[start]' => (clone $now)->modify('-1 year')->format('Y-m-d'),
            'date[end]' => $now->format('Y-m-d'),
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('tr>td>span', 'CHRS XXX');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
