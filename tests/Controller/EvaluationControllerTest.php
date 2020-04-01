<?php

namespace App\Tests\Controller;

use App\Entity\EvaluationGroup;
use Symfony\Component\DomCrawler\Crawler;
use App\Tests\Controller\ControllerTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EvaluationControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ControllerTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/personFixturesTest.yaml",
            dirname(__DIR__) . "/DataFixturesTest/SupportFixturesTest.yaml",
            dirname(__DIR__) . "/DataFixturesTest/EvaluationFixturesTest.yaml"
        ]);

        $this->createLoggedUser($this->dataFixtures);

        $this->supportGroup = $this->dataFixtures["supportGroup1"];
    }


    public function testCreateEvaluationGroup()
    {
        $this->client->request("GET", $this->generateUri("support_evaluation_show", [
            "id" => ($this->dataFixtures["supportGroup1"])->getId()
        ]));

        $this->client->followRedirect();

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Évaluation sociale");
    }

    public function testShowEvaluation()
    {
        $this->client->request("GET", $this->generateUri("support_evaluation_show", [
            "id" => ($this->dataFixtures["supportGroupWithEval"])->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Évaluation sociale");
    }
}
