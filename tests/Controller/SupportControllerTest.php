<?php

namespace App\Tests\Controller;

use App\Entity\SupportGroup;
use Symfony\Component\DomCrawler\Crawler;
use App\Tests\Controller\ControllerTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SupportControllerTest extends WebTestCase
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
            dirname(__DIR__) . "/DataFixturesTest/SupportFixturesTest.yaml"
        ]);

        $this->createLoggedUser($this->dataFixtures);

        $this->supportGroup = $this->dataFixtures["supportGroup1"];
    }

    public function testViewListSupportsIsUp()
    {
        /** @var Crawler */
        $crawler = $this->client->request("GET", $this->generateUri("supports"));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Suivis");
    }

    public function testNewSupportGroupIsUp()
    {
        $this->client->request("GET", $this->generateUri("support_new", [
            "id" => ($this->dataFixtures["groupPeople"])->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Nouveau suivi social");
    }

    public function testEditSupportGroupIsUp()
    {
        $this->client->request("GET", $this->generateUri("support_edit", [
            "id" => ($this->dataFixtures["supportGroup1"])->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Suivi social");
    }

    public function testSuccessDeleteSupport()
    {
        $this->client->request("GET", $this->generateUri("support_delete", [
            "id" => ($this->dataFixtures["supportGroup1"])->getId()
        ]));

        $this->client->followRedirect();

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Groupe");
        $this->assertSelectorExists(".alert.alert-warning");
    }

    public function testEditSupportGroupleWithPeopleIsUp()
    {
        $this->client->request("GET", $this->generateUri("support_pers_edit", [
            "id" => ($this->dataFixtures["supportGroup1"])->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h2", "Personnes rattachées au suivi social");
    }

    public function testAddPeopleInSupportIsUp()
    {
        $this->client->request("GET", $this->generateUri("support_add_people", [
            "id" => ($this->dataFixtures["supportGroup1"])->getId()
        ]));

        $this->client->followRedirect();

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h2", "Personnes rattachées au suivi social");
        $this->assertSelectorExists(".alert.alert-warning");
    }

    // public function testRemoveSupportPerson()
    // {
    //     $supportPerson = ($this->dataFixtures["supportPerson1"]);
    //     $csrfToken = $this->client->getContainer()->get("security.csrf.token_manager")->getToken("remove" . $supportPerson->getId());

    //     $this->client->request("GET", $this->generateUri("remove_support_pers", [
    //         "id" => ($this->dataFixtures["supportGroup1"])->getId(),
    //         "support_pers_id" => $supportPerson->getid(),
    //         "_token" => $csrfToken
    //     ]));

    //     $result = json_decode($this->client->getResponse()->getContent(), true);

    //     $this->assertSame(200, $result["code"]);
    // }

    public function testExportUsUp()
    {
        $this->client->request("GET", $this->generateUri("export"));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Export des données");
    }
}
