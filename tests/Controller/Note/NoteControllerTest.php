<?php

namespace App\Tests\Controller\Note;

use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class NoteControllerTest extends WebTestCase
{
    use AppTestTrait;

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

        $this->client = $this->createClient();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testSearchNotesIsSuccessful()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userRoleUser']);

        // Page is up
        $this->client->request('GET', '/notes');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Notes');

        // Search is up
        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [
            'content' => 'Note test',
            'type' => 1,
            'status' => 1,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThanOrEqual(4, $crawler->filter('td[scope="row"]')->count());
    }

    public function testSearchSupportNotesIsSuccessful()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/notes");

        // Page is up
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Notes sociales');

        // Search is successful
        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [
            'content' => 'Note test',
            'type' => 1,
            'status' => 1,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThanOrEqual(5, $crawler->filter('div[data-note-id]')->count());
    }

    public function testExportNotesOfSupportIsSuccessful()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/notes");

        $this->client->submitForm('export', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame('application/vnd.ms-word', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreateNoteIsSuccessful()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->supportGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/notes");
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

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('create', $content['action']);
    }

    public function testUpdateNoteIsSuccessful()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->supportGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/notes");
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

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('update', $content['action']);
    }

    public function testUpdateNoteWithOtherUserIsSuccessful()
    {
        $this->loadFixtures();
        $this->createLogin($this->fixtures['user4']);

        $crawler = $this->client->request('GET', '/support/1/notes');

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

    public function testDeleteNote()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->note->getId();
        $this->client->request('GET', "/note/$id/delete");

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('delete', $data['action']);
    }

    public function testExportNoteIsSuccessful()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->note->getId();

        // Export to Word
        $this->client->request('GET', "/note/$id/export/word");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame('application/vnd.ms-word', $this->client->getResponse()->headers->get('content-type'));

        // Export to PDF
        $this->client->request('GET', "/note/$id/export/pdf");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame('application/pdf', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testGenerateNoteEvaluationIsFailed()
    {
        $this->loadFixtures();

        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->note->getId();
        $this->client->request('GET', "/support/$id/note/new_evaluation");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'Il n\'y a pas d\'évaluation sociale créée pour ce suivi.');
    }

    public function testGenerateNoteEvaluationIsSuccessful()
    {
        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/EvaluationFixturesTest.yaml',
        ]);

        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->fixtures['supportGroupWithEval']->getId();
        $this->client->request('GET', "/support/$id/evaluation/view");

        $this->client->submitForm('send');

        $this->client->request('GET', "/support/$id/note/new_evaluation");
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h3.card-title', 'Grille d\'évaluation sociale');
    }

    protected function loadFixtures(): void
    {
        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/NoteFixturesTest.yaml',
        ]);

        $this->supportGroup = $this->fixtures['supportGroup1'];
        $this->note = $this->fixtures['note1'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $this->fixtures = null;

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();
    }
}
