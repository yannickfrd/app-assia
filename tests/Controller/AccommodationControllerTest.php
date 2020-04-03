<?php

namespace App\Tests\Controller;

use App\Entity\Service;
use App\Entity\Accommodation;
use Symfony\Component\DomCrawler\Crawler;
use App\Tests\AppTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccommodationControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var Service */
    protected $service;

    /** @var Accommodation */
    protected $accommodation;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/AccommodationFixturesTest.yaml"
        ]);

        $this->createLogin($this->dataFixtures["userSuperAdmin"]);

        $this->service = $this->dataFixtures["service"];
        $this->accommodation = $this->dataFixtures["accommodation1"];
    }

    public function testListAccommodationsIsUp()
    {
        $this->client->request("GET", $this->generateUri("admin_accommodations"));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Groupes de places");
    }

    public function testNewAccommodationIsUp()
    {
        $this->client->request("GET", $this->generateUri("service_accommodation_new", [
            "id" => $this->service->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Nouveau groupe de places");
    }

    public function testEditAccommodationisUp()
    {
        $this->client->request("GET", $this->generateUri("accommodation_edit", [
            "id" => $this->accommodation->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", $this->accommodation->getName());
    }

    public function testDeleteAccommodation()
    {
        $this->client->request("GET", $this->generateUri("admin_accommodation_delete", [
            "id" => $this->accommodation->getId()
        ]));

        $this->client->followRedirect();

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", $this->accommodation->getService()->getName());
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
