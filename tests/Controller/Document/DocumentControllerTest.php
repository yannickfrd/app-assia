<?php

namespace App\Tests\Controller\Document;

use App\Entity\Support\Document;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class DocumentControllerTest extends WebTestCase
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

    /** @var Document */
    protected $document;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();

        /* @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/DocumentFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/TagFixturesTest.yaml',
        ]);

        $this->supportGroup = $this->fixtures['supportGroup1'];
        $this->document = $this->fixtures['document1'];
        $this->documentsDirectory = dirname(__DIR__).'/../../public/uploads/documents/';
    }

    public function testSearchDocumentsISuccessful()
    {
        $this->createLogin($this->fixtures['userSuperAdmin']);

        $this->client->request('GET', '/admin/documents');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Documents');

        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [
            'name' => 'Document',
            // 'tags' => ['1'],
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThanOrEqual(2, $crawler->filter('tr')->count());
    }

    public function tesSearchSupportDocumentsIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/documents");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Documents');

        $this->client->submitForm('search', [
            'search[name]' => 'Document',
            // 'search[tags]' => ['1'],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('table tbody tr td[data-document="name"]', 'Document');
    }

    public function testCreateNewDocumentIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        // Fail
        $id = $this->supportGroup->getId();
        $this->client->request('POST', "/support/$id/document/new");

        $contentResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame('danger', $contentResponse['alert']);

        // Success
        $this->uploadFile();
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('success', $content['alert']);

        $documentRepo = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Document::class);
        $document = $documentRepo->find($content['data'][0]['id']);
        $file = $this->documentsDirectory.$document->getCreatedAt()->format('Y/m/d/').$document->getPeopleGroup()->getId().'/'.$document->getInternalFileName();
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function testDownloadDocumentIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->document->getId();

        // Fail
        $this->client->request('GET', "/document/$id/download");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('div.alert.alert-danger');

        // Success
        $newFile = $this->moveFile();

        $this->client->request('GET', "/document/$id/download");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertResponseHasHeader('content-name', $this->document->getInternalFileName());

        if (file_exists($newFile)) {
            unlink($newFile);
        }
    }

    public function testDownloadDocumentsIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $newFile = $this->moveFile();

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/documents");

        $_POST['items'] = '['.$this->document->getId().']';

        $this->client->submitForm('action-validate', [
            'action[type]' => 1,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertResponseHasHeader('Content-Type', 'application/zip');

        if (file_exists($newFile)) {
            unlink($newFile);
        }
    }

    public function testEditDocumentIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

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

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame('update', $contentResponse['action']);
    }

    public function testDeleteDocumentIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->document->getId();
        $this->client->request('GET', "/document/$id/delete");

        $contentResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('delete', $contentResponse['action']);
    }

    private function uploadFile(): Response
    {
        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/documents");

        $uploadedFile = new UploadedFile(
            dirname(__DIR__).'/../DataFixturesTest/files/doc.docx',
            'doc.docx', null, null, true
        );

        $this->client->submitForm('send', [
            'dropzone_document[files]' => [$uploadedFile],
        ]);

        return $this->client->getResponse();
    }

    private function moveFile(): ?string
    {
        $file = dirname(__DIR__).'/../DataFixturesTest/files/doc.docx';
        if (!file_exists($file)) {
            return null;
        }

        $newPath = $this->documentsDirectory.$this->document->getCreatedAt()->format('Y/m/d/').$this->document->getPeopleGroup()->getId().'/';
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

        $this->client = null;
        $this->fixtures = null;
    }
}
