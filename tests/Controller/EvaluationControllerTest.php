<?php

namespace App\Tests\Controller;

use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class EvaluationControllerTest extends WebTestCase
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
            dirname(__DIR__).'/DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/EvaluationFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->supportGroup = $this->dataFixtures['supportGroup1'];
    }

    public function testCreateEvaluationGroup()
    {
        $this->client->request('GET', $this->generateUri('support_evaluation_show', [
            'id' => ($this->dataFixtures['supportGroup1'])->getId(),
        ]));
        // $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');
    }

    public function testShowEvaluation()
    {
        $this->client->request('GET', $this->generateUri('support_evaluation_show', [
            'id' => ($this->dataFixtures['supportGroupWithEval'])->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');
    }

    public function testEditEvaluation()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('support_evaluation_show', [
            'id' => ($this->dataFixtures['supportGroupWithEval'])->getId(),
        ]));

        $form = $crawler->selectButton('send')->form([]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
