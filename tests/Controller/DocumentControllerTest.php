<?php

namespace App\Tests\Controller;

use App\Entity\Document;
use Symfony\Component\DomCrawler\Crawler;
use App\Tests\Controller\ControllerTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ControllerTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Document */
    protected $document;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/DocumentFixturesTest.yaml",
        ]);

        $this->createLoggedUser($this->dataFixtures);

        $this->supportGroup = $this->dataFixtures["supportGroup"];
        $this->document = $this->dataFixtures["document1"];
    }

    public function testListDocumentsIsUp()
    {
        /** @var Crawler */
        $crawler = $this->client->request("GET", $this->generateUri("support_documents", [
            "id" => $this->supportGroup->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Documents");
    }

    public function testFailToCreateNewDocument()
    {
        $this->client->request("POST", $this->generateUri("document_new", [
            "id" => $this->supportGroup->getId()
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame("danger", $data["alert"]);
    }

    public function testFailToEditDocument()
    {
        $this->client->request("POST", $this->generateUri("document_edit", [
            "id" => $this->document->getId()
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame("danger", $data["alert"]);
    }

    public function testDeleteDocument()
    {
        $this->client->request("GET", $this->generateUri("document_delete", [
            "id" => $this->document->getId()
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame("delete", $data["action"]);
    }
}
