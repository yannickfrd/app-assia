<?php

namespace App\Tests\Controller;

use App\Entity\Pole;
use Symfony\Component\DomCrawler\Crawler;
use App\Tests\AppTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PoleControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var Pole */
    protected $pole;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/ServiceFixturesTest.yaml"
        ]);

        $this->createLogin($this->dataFixtures["userSuperAdmin"]);

        $this->pole = $this->dataFixtures["pole"];
    }

    public function testListPolesIsUp()
    {
        $this->client->request("GET", $this->generateUri("poles"));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Pôles");
    }

    public function testNewPoleIsUp()
    {
        $this->client->request("GET", $this->generateUri("pole_new"));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Nouveau pôle");
    }

    public function testEditPoleIsUp()
    {
        $this->client->request("GET", $this->generateUri("pole_edit", [
            "id" => $this->pole->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", $this->pole->getName());
    }

    // protected function tearDown()
    // {
    //     parent::tearDown();
    //     $this->client = null;
    //     $this->dataFixtures = null;
    // }
}
