<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Support\SupportGroup;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndicatorControllerTest extends WebTestCase
{
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

        $this->client = static::createClient();
        $this->client->followRedirects();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/evaluation_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/note_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/rdv_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/document_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/payment_fixtures_test.yaml',
        ]);

        $this->client->loginUser($this->fixtures['user_super_admin']);
    }

    public function testPageIndicatorsIsUp(): void
    {
        $this->client->request('GET', '/daily_indicators');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Indicateurs quotidiens d\'activité');
    }

    public function testSearchIndicatorServicesIsSuccessful(): void
    {
        $this->client->request('GET', '/indicator/services');

        // Page is up
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Indicateurs d\'activité des services');

        // Search is sucessful
        $now = new \DateTime();
        $this->client->submitForm('search', [
            'status' => [SupportGroup::STATUS_IN_PROGRESS],
            'date[start]' => (clone $now)->modify('-1 year')->format('Y-m-d'),
            'date[end]' => $now->format('Y-m-d'),
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('tr>td>span', 'AVDL');
    }

    public function testSocialIndicatorIsSuccessful(): void
    {
        $this->client->request('GET', '/indicators/social');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Indicateurs sociaux');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
