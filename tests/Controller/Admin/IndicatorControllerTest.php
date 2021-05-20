<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Support\SupportGroup;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class IndicatorControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $data;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp()
    {
        $this->data = $this->loadFixtureFiles([
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

        $this->createLogin($this->data['userSuperAdmin']);
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
        $this->data = null;
    }
}
