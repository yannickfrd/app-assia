<?php

namespace App\Tests\Controller;

use App\Entity\SupportGroup;
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
    protected $dataFixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/EvaluationFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/NoteFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/RdvFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/DocumentFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/ContributionFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userSuperAdmin']);
    }

    public function testPageIndicatorsIsUp()
    {
        $this->client->request('GET', $this->generateUri('daily_indicators'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Indicateurs quotidiens d\'activité');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
