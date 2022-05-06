<?php

namespace App\Tests\Controller\Note;

use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;

class NoteControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Note */
    protected $note;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testSearchNotesIsSuccessful(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->fixtures['john_user']);

        // Page is up
        $this->client->request('GET', '/notes');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Notes');

        // Search is up
        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [
            'content' => 'Note test',
            'type' => 1,
            'status' => 1,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThanOrEqual(4, $crawler->filter('tbody td')->count());
    }

    /**
     * @dataProvider provideView
     */
    public function testSearchSupportNotesIsSuccessful(string $view): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/notes/$view");

        // Page is up
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Notes sociales');

        // Search is successful
        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [
            'content' => 'Note test',
            'type' => 1,
            'status' => 1,
        ]);

        $this->assertResponseIsSuccessful();
        $selector = ('card-view' === $view) ? 'div[data-note-id]' : 'tbody tr';
        $this->assertGreaterThanOrEqual(5, $crawler->filter($selector)->count());
    }

    /**
     * @dataProvider provideView
     */
    public function testExportNotesOfSupportIsSuccessful(string $view): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/notes/$view");

        $this->client->submitForm('export', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertSame('application/vnd.ms-word', $this->client->getResponse()->headers->get('content-type'));
    }

    /**
     * @dataProvider provideView
     */
    public function testCreateNoteIsSuccessful(string $view): void
    {
        $this->loadFixtures();

        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->supportGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/notes/$view");
        $csrfToken = $crawler->filter('#note__token')->attr('value');

        // Fail without token
        $this->client->request('POST', "/support/$id/note/new");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);

        // Success with token
        $this->client->request('POST', "/support/$id/note/new", [
            'note' => [
                'title' => 'Note test',
                'content' => 'Contenu de la note',
                'type' => Note::TYPE_NOTE,
                'status' => Note::STATUS_DEFAULT,
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('create', $content['action']);
    }

    /**
     * @dataProvider provideView
     */
    public function testEditNoteIsSuccessful(string $view): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->supportGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/notes/$view");
        $csrfToken = $crawler->filter('#note__token')->attr('value');

        $id = $this->note->getId();

        // Fail without token
        $this->client->request('POST', "/note/$id/edit");
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $data['alert']);

        // Success with token
        $this->client->request('POST', "/note/$id/edit", [
            'note' => [
                'title' => 'Note test update',
                'content' => 'Contenu de la note',
                'type' => Note::TYPE_NOTE,
                'status' => Note::STATUS_DEFAULT,
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('update', $content['action']);
    }

    /**
     * @dataProvider provideView
     */
    public function testUpdateNoteWithOtherUserIsSuccessful(string $view): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->fixtures['user4']);

        $crawler = $this->client->request('GET', "/support/1/notes/$view");

        $this->client->request('POST', '/note/1/edit', [
            'note' => [
                'title' => 'Note test update',
                'content' => 'Contenu de la note',
                '_token' => $crawler->filter('#note__token')->attr('value'),
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('update', $content['action']);
    }

    public function testDeleteNote(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->note->getId();
        $this->client->request('GET', "/note/$id/delete");

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('delete', $data['action']);
    }

    /**
     * @dataProvider provideView
     */
    public function testRestoreNoteIsSuccessful(string $view)
    {
        $this->loadFixtures();
        $this->client->loginUser($this->fixtures['john_user']);

        $noteId = $this->note->getId();
        $this->client->request('GET', "/note/$noteId/delete");

        // After delete a note
        $id = $this->supportGroup->getId();
        $crawler = $this->client->request('GET', "/support/$id/notes/$view", [
            'deleted' => ['deleted' => true],
        ]);
        $this->assertResponseIsSuccessful();
        $selector = ('card-view' === $view) ? 'div[data-note-id]' : 'tbody tr';
        $this->assertGreaterThanOrEqual(1, $crawler->filter($selector)->count());

        $this->client->request('GET', "/note/$noteId/restore");
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('restore', $content['action']);

        // After restore a note
        $crawler = $this->client->request('GET', "/support/$id/notes/$view", [
            'deleted' => ['deleted' => true],
        ]);
        $this->assertGreaterThanOrEqual(0, $crawler->filter($selector)->count());
    }

    public function testExportNoteIsSuccessful(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->note->getId();

        // Export to Word
        $this->client->request('GET', "/note/$id/export/word");

        $this->assertResponseIsSuccessful();
        $this->assertSame('application/vnd.ms-word', $this->client->getResponse()->headers->get('content-type'));

        // Export to PDF
        $this->client->request('GET', "/note/$id/export/pdf");

        $this->assertResponseIsSuccessful();
        $this->assertSame('application/pdf', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testGenerateNoteEvaluationIsFailed(): void
    {
        $this->loadFixtures();
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->note->getId();
        $this->client->request('GET', "/support/$id/note/new_evaluation");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-warning', 'Il n\'y a pas d\'évaluation sociale créée pour ce suivi.');
    }

    public function testGenerateNoteEvaluationIsSuccessful(): void
    {
        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/evaluation_fixtures_test.yaml',
        ]);

        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->fixtures['support_group_with_eval']->getId();
        $this->client->request('GET', "/support/$id/evaluation/show");

        $this->client->submitForm('send');

        $this->client->request('GET', "/support/$id/note/new_evaluation");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3.card-title', 'Grille d\'évaluation sociale');
    }

    public function provideView(): \Generator
    {
        yield ['card-view'];
        yield ['table-view'];
    }

    protected function loadFixtures(): void
    {
        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/note_fixtures_test.yaml',
        ]);

        $this->supportGroup = $this->fixtures['support_group1'];
        $this->note = $this->fixtures['note1'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();
    }
}
