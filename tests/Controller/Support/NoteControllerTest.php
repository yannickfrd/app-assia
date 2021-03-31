<?php

namespace App\Tests\Controller;

use App\Entity\Support\Note;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class NoteControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

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
            dirname(__DIR__).'/../DataFixturesTest/NoteFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->supportGroup = $this->dataFixtures['supportGroup'];
        $this->note = $this->dataFixtures['note1'];
    }

    public function testListNotesIsUp()
    {
        $this->client->request('GET', '/notes');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Notes');
    }

    public function testSearchNotesIsSuccessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', '/notes');

        $form = $crawler->selectButton('search')->form([
            'content' => 'Note 666',
            'type' => 1,
            'status' => 1,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testSupportListNotesIsUp()
    {
        $this->client->request('GET', "support/{$this->supportGroup->getId()}/notes");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Notes sociales');
    }

    public function testSearchSupportNotesIsSuccessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', "support/{$this->supportGroup->getId()}/notes");

        $form = $crawler->selectButton('search')->form([
            'content' => 'Note 666',
            'type' => 1,
            'status' => 1,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Notes sociales');
    }

    public function testExportNotesOfSupportIsSuccessful()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', "support/{$this->supportGroup->getId()}/notes");

        $form = $crawler->selectButton('export')->form();

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame('application/vnd.ms-word', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testFailToCreateNewNote()
    {
        $this->client->request('POST', $this->generateUri('note_new', [
            'id' => $this->supportGroup->getId(),
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('danger', $data['alert']);
    }

    public function testFailToEditNote()
    {
        $this->client->request('POST', $this->generateUri('note_edit', [
            'id' => $this->note->getId(),
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('danger', $data['alert']);
    }

    public function testDeleteNote()
    {
        $this->client->request('GET', $this->generateUri('note_delete', [
            'id' => $this->note->getId(),
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('delete', $data['action']);
    }

    public function testExportNote()
    {
        $this->client->request('GET', $this->generateUri('note_export_word', [
            'id' => $this->note->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
