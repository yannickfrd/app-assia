<?php

namespace App\Tests\Controller\Note;

use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class NoteControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $data;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Note */
    protected $note;

    protected function setUp(): void
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/NoteFixturesTest.yaml',
        ]);

        $this->supportGroup = $this->data['supportGroup1'];
        $this->note = $this->data['note1'];
    }

    public function testSearchNotesIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        // Page is up
        $this->client->request('GET', '/notes');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Notes');

        // Search is up
        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [
            'content' => 'Note 666',
            'type' => 1,
            'status' => 1,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThanOrEqual(4, $crawler->filter('td[scope="row"]')->count());
    }

    public function testSearchSupportNotesIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/notes");

        // Page is up
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Notes sociales');

        // Search is successful
        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [
            'content' => 'Note 666',
            'type' => 1,
            'status' => 1,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThanOrEqual(5, $crawler->filter('div[data-note-id]')->count());
    }

    public function testExportNotesOfSupportIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/notes");

        $this->client->submitForm('export', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame('application/vnd.ms-word', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreateNoteIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

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
        $this->createLogin($this->data['userRoleUser']);

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

    public function testDeleteNote()
    {
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->note->getId();
        $this->client->request('GET', "/note/$id/delete");

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('delete', $data['action']);
    }

    public function testExportNoteIsSuccessful()
    {
        $this->createLogin($this->data['userRoleUser']);

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
        $this->createLogin($this->data['userRoleUser']);

        $id = $this->note->getId();
        $this->client->request('GET', "/support/$id/note/new_evaluation");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'Il n\'y a pas d\'évaluation sociale créée pour ce suivi.');
    }

    public function testGenerateNoteEvaluationIsSuccessful()
    {
        $data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/EvaluationFixturesTest.yaml',
        ]);

        $this->createLogin($data['userRoleUser']);

        $id = $data['supportGroupWithEval']->getId();
        $this->client->request('GET', "/support/$id/evaluation/view");

        $this->client->submitForm('send', []);

        $this->client->request('GET', "/support/$id/note/new_evaluation");
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h3.card-title', 'Grille d\'évaluation sociale');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->data = null;

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();
    }
}
