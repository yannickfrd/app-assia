<?php

namespace App\Tests\Controller;

use App\Entity\Support\Document;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class DocumentControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

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
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/DocumentFixturesTest.yaml',
        ]);

        $this->supportGroup = $this->dataFixtures['supportGroup'];
        $this->document = $this->dataFixtures['document1'];
        $this->documentsDirectory = dirname(__DIR__).'/../../public/uploads/documents/';
    }

    public function testPageDocumentsIsUp()
    {
        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->client->request('GET', $this->generateUri('documents'));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Documents');
    }

    public function testSupportDocumentsIsUp()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->client->request('GET', $this->generateUri('support_documents', [
            'id' => $this->supportGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Documents');
    }

    public function tesSearchDocumentIsSuccessful()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('support_documents', [
            'id' => $this->supportGroup->getId(),
        ]));

        $form = $crawler->selectButton('search')->form([
            'search[name]' => 'Document',
            'search[type]' => 1,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('table tbody tr td[data-document="name"]', 'Document');
    }

    public function testCreateNewDocumentIsFailed()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->client->request('POST', $this->generateUri('document_new', [
            'id' => $this->supportGroup->getId(),
        ]));

        $contentResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame('danger', $contentResponse['alert']);
    }

    public function testCreateNewDocumentIsSuccessful()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->uploadFile();
        $contentResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('success', $contentResponse['alert']);

        $repo = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Document::class);
        $document = $repo->find($contentResponse['data'][0]['id']);
        $file = $this->documentsDirectory.$document->getCreatedAt()->format('Y/m/d/').$document->getPeopleGroup()->getId().'/'.$document->getInternalFileName();
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function testDownloadDocumentIsFailed()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->client->request('GET', "document/{$this->document->getId()}/download");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorExists('div.alert.alert-danger');
    }

    public function testDownloadDocumentIsSuccessful()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $newFile = $this->moveFile();

        $this->client->request('GET', "document/{$this->document->getId()}/download");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertResponseHasHeader('content-name', $this->document->getInternalFileName());

        if (file_exists($newFile)) {
            unlink($newFile);
        }
    }

    public function testDownloadDocumentsIsSuccessful()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $newFile = $this->moveFile();

        $crawler = $this->client->request('GET', "support/{$this->supportGroup->getId()}/documents");

        $_POST['items'] = '['.$this->document->getId().']';

        $form = $crawler->selectButton('action-validate')->form([
            'action[type]' => 1,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertResponseHasHeader('Content-Type', 'application/zip');

        if (file_exists($newFile)) {
            unlink($newFile);
        }
    }

    public function testEditDocumentIsFailed()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->client->request('POST', "document/{$this->document->getId()}/edit");

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('danger', $data['alert']);
    }

    public function testEditDocumentIsSuccessful()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $crawler = $this->client->request('GET', "support/{$this->supportGroup->getId()}/documents");

        $content = $this->client->getResponse()->getContent();
        $content = str_replace('action="/document/__id__/edit"', 'action="/document/'.$this->document->getId().'/edit"', $content);
        $this->client->getResponse()->setContent($content);

        $crawler->clear();
        $crawler->addContent($this->client->getResponse()->getContent());

        // $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('document');
        $form = $crawler->selectButton('document_update')->form([
            'document[name]' => 'Document',
            'document[type]' => 1,
            // '_token' => $csrfToken,
        ]);

        $this->client->submit($form);

        $contentResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame('update', $contentResponse['action']);
    }

    public function testDeleteDocumentIsSuccessful()
    {
        $this->createLogin($this->dataFixtures['userRoleUser']);

        $this->client->request('GET', "document/{$this->document->getId()}/delete");

        $contentResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('delete', $contentResponse['action']);
    }

    private function uploadFile(): Response
    {
        $crawler = $this->client->request('GET', "/support/{$this->supportGroup->getId()}/documents");

        $uploadedFile = new UploadedFile(
            dirname(__DIR__).'/../DataFixturesTest/files/doc.docx',
            'doc.docx', null, null, true
        );

        $form = $crawler->selectButton('send')->form([
            'dropzone_document[files]' => [$uploadedFile],
        ]);

        $this->client->submit($form);

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
        $this->dataFixtures = null;
    }
}
