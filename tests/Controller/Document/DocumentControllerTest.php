<?php

namespace App\Tests\Controller\Document;

use App\Entity\Support\Document;
use App\Entity\Support\SupportGroup;
use App\Repository\Support\DocumentRepository;
use App\Service\File\FileConverter;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class DocumentControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Document */
    protected $document;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/document_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/tag_fixtures_test.yaml',
        ]);

        $this->supportGroup = $this->fixtures['support_group1'];
        $this->document = $this->fixtures['document1'];
        $this->documentsDirectory = dirname(__DIR__).'/../../public/uploads/documents/';
    }

    public function testSearchDocumentsISuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $this->client->request('GET', '/admin/documents');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Documents');

        $crawler = $this->client->submitForm('search', [
            'name' => 'Document',
            // 'tags' => ['1'],
        ], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThanOrEqual(2, $crawler->filter('tr')->count());
    }

    public function tesSearchSupportDocumentsIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/documents");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Documents');

        $this->client->submitForm('search', [
            'search[name]' => 'Document',
            // 'search[tags]' => ['1'],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('table tbody tr td[data-cell="name"]', 'Document');
    }

    public function testCreateNewDocumentIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        // Fail
        $id = $this->supportGroup->getId();
        $this->client->request('POST', "/support/$id/document/create");

        $contentResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertSame('danger', $contentResponse['alert']);

        // Success
        $this->uploadFile();
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('success', $content['alert']);

        /** @var DocumentRepository $documentRepo */
        $documentRepo = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Document::class);
        $document = $documentRepo->find($content['documents'][0]['id']);
        $file = $this->documentsDirectory.$document->getFilePath();
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function testPreviewDocumentIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->document->getId();
        $documentName = $this->document->getName();

        // Fail
        $this->client->request('GET', "/document/$id/preview");

        $contentResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertSame("Le fichier « $documentName » n'existe pas.", $contentResponse['msg']);

        // Success
        $newFile = $this->moveFile();

        $this->client->request('GET', "/document/$id/preview");

        $this->assertResponseIsSuccessful();
        $this->assertResponseHasHeader('content-name', $this->document->getInternalFileName());

        /** @var FileConverter $fileConverter */
        $fileConverter = $this->client->getContainer()->get(FileConverter::class);

        $convertedFile = $fileConverter->convert($this->document);

        if (file_exists($convertedFile)) {
            unlink($convertedFile);
        }

        if (file_exists($newFile)) {
            unlink($newFile);
        }
    }

    public function testDownloadDocumentIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->document->getId();
        $documentName = $this->document->getName();

        // Fail
        $this->client->request('GET', "/document/$id/download");

        $contentResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertSame("Le fichier « $documentName » n'existe pas.", $contentResponse['msg']);

        // Success
        $newFile = $this->moveFile();

        $this->client->request('GET', "/document/$id/download");

        $this->assertResponseIsSuccessful();
        $this->assertResponseHasHeader('content-name', $this->document->getInternalFileName());

        if (file_exists($newFile)) {
            unlink($newFile);
        }
    }

    public function testDownloadDocumentsIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $newFile = $this->moveFile();

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/documents");

        $_POST['items'] = '['.$this->document->getId().']';

        $this->client->submitForm('action-validate', [
            'action[type]' => 1,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHasHeader('Content-Type', 'application/zip');

        if (file_exists($newFile)) {
            unlink($newFile);
        }
    }

    public function testShowDocumentIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->document->getId();

        $this->client->request('GET', "/document/$id/show");

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertSame('show', $content['action']);
    }

    public function testEditDocumentIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->document->getId();

        // Fail
        $this->client->request('POST', "/document/$id/edit");

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('danger', $data['alert']);

        // Success
        $crawler = $this->client->request('GET', "/support/$id/documents");

        $content = $this->client->getResponse()->getContent();
        $content = str_replace('action="/document/__id__/edit"', 'action="/document/'.$this->document->getId().'/edit"', $content);
        $this->client->getResponse()->setContent($content);

        $crawler->clear();
        $crawler->addContent($this->client->getResponse()->getContent());

        $this->client->submitForm('document_update', [
            'document[name]' => 'Document',
            'document[tags]' => ['1'],
        ]);

        $contentResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertResponseIsSuccessful();
        $this->assertSame('update', $contentResponse['action']);
    }

    public function testDeleteDocumentIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->document->getId();
        $this->client->request('GET', "/document/$id/delete");

        $contentResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('delete', $contentResponse['action']);
    }

    public function testRestoreDocumentIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $documentId = $this->document->getId();
        $this->client->request('GET', "/document/$documentId/delete");

        // After delete a document
        $id = $this->supportGroup->getId();
        $crawler = $this->client->request('GET', "/support/$id/documents", [
            'search' => ['deleted' => ['deleted' => true]],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('tbody tr')->count());

        $this->client->request('GET', "/document/$documentId/restore");
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('restore', $content['action']);

        // After restore a document
        $crawler = $this->client->request('GET', "/support/$id/documents", [
            'search' => ['deleted' => ['deleted' => true]],
        ]);
        $this->assertSame(0, $crawler->filter('tbody tr')->count());
    }

    private function uploadFile(): Response
    {
        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/documents");

        $uploadedFile = new UploadedFile(
            dirname(__DIR__).'/../fixtures/files/doc.docx',
            'doc.docx', null, null, true
        );

        $this->client->submitForm('send', [
            'dropzone_document[files]' => [$uploadedFile],
        ]);

        return $this->client->getResponse();
    }

    private function moveFile(): ?string
    {
        $file = dirname(__DIR__).'/../fixtures/files/doc.docx';
        if (!file_exists($file)) {
            return null;
        }

        $newPath = $this->documentsDirectory.$this->document->getPath();
        $newFile = $newPath.$this->document->getInternalFileName();

        if (!file_exists($newPath)) {
            mkdir($newPath, 0700, true);
        }
        if (!file_exists($newFile)) {
            copy($file, $newFile);
        }

        return $newFile;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
