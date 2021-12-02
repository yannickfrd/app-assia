<?php

namespace App\Tests\Controller\Admin;

use App\Tests\AppTestTrait;
use App\Entity\Support\SupportGroup;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class IndicatorControllerTest extends WebTestCase
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

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = $this->createClient();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/EvaluationFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/NoteFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/RdvFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/DocumentFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PaymentFixturesTest.yaml',
        ]);

        $this->createLogin($this->fixtures['userSuperAdmin']);
    }

    public function testPageIndicatorsIsUp()
    {
        $this->client->request('GET', '/daily_indicators');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Indicateurs quotidiens d\'activité');
    }

    public function testSearchIndicatorServicesIsSuccessful()
    {
        $this->client->request('GET', '/indicator/services');

        // Page is up
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Indicateurs d\'activité des services');

        // Search is sucessful
        $now = new \DateTime();
        $this->client->submitForm('search', [
            'status' => [SupportGroup::STATUS_IN_PROGRESS],
            'date[start]' => (clone $now)->modify('-1 year')->format('Y-m-d'),
            'date[end]' => $now->format('Y-m-d'),
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('tr>td>span', 'AVDL');
    }

    public function testSocialIndicatorIsSuccessful()
    {
        $this->client->request('GET', '/indicators/social');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Indicateurs sociaux');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        $this->client = null;
        $this->fixtures = null;
    }
}
