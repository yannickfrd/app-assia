<?php

namespace App\Tests\Controller;

use App\Entity\Note;
use Symfony\Component\DomCrawler\Crawler;
use App\Tests\Controller\ControllerTestTrait;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NoteControllerTest extends WebTestCase
{
    use FixturesTrait;
    use ControllerTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Note */
    protected $note;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/NoteFixturesTest.yaml",
        ]);

        $this->createLoggedUser($this->dataFixtures);

        $this->supportGroup = $this->dataFixtures["supportGroup"];
        $this->note = $this->dataFixtures["note1"];
    }

    public function testListNotesIsUp()
    {
        /** @var Crawler */
        $crawler = $this->client->request("GET", $this->generateUri("support_notes", [
            "id" => $this->supportGroup->getId()
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains("h1", "Notes sociales");
    }

    public function testFailToCreateNewNote()
    {
        $this->client->request("POST", $this->generateUri("note_new", [
            "id" => $this->supportGroup->getId()
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame("danger", $data["alert"]);
    }

    public function testFailToEditNote()
    {
        $this->client->request("POST", $this->generateUri("note_edit", [
            "id" => $this->note->getId()
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame("danger", $data["alert"]);
    }

    public function testDeleteNote()
    {
        $this->client->request("GET", $this->generateUri("note_delete", [
            "id" => $this->note->getId()
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame("delete", $data["action"]);
    }
}
