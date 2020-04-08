<?php

namespace App\Tests\Controller;

use App\Entity\Document;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
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
            dirname(__DIR__).'/DataFixturesTest/DocumentFixturesTest.yaml',
        ]);

        $this->createLogin($this->dataFixtures['userSuperAdmin']);

        $this->supportGroup = $this->dataFixtures['supportGroup'];
        $this->document = $this->dataFixtures['document1'];
    }

    public function testListDocumentsIsUp()
    {
        $this->client->request('GET', $this->generateUri('support_documents', [
            'supportId' => $this->supportGroup->getId(),
        ]));

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Documents');
    }

    public function testSearchDocument()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generateUri('support_documents', [
            'supportId' => $this->supportGroup->getId(),
        ]));

        $form = $crawler->selectButton('search')->form([
            'document_search[name]' => 'Document 666',
            'document_search[type]' => 1,
        ]);

        $this->client->submit($form);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->assertSelectorTextContains('table tbody tr td', 'Document 666');
    }

    public function testFailToCreateNewDocument()
    {
        $this->client->request('POST', $this->generateUri('document_new', [
            'supportId' => $this->supportGroup->getId(),
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('danger', $data['alert']);
    }

    public function testFailToEditDocument()
    {
        $this->client->request('POST', $this->generateUri('document_edit', [
            'id' => $this->document->getId(),
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('danger', $data['alert']);
    }

    // public function testEditDocument()
    // {
    //     $crawler = $this->client->request('GET', $this->generateUri('support_documents', [
    //         'supportId' => $this->supportGroup->getId(),
    //     ]));

    //     $form = $crawler->selectButton('js-btn-save')->form([]);

    //     $this->client->request('POST', $this->generateUri('document_edit', [
    //         'id' => $this->document->getId(),
    //     ]), [
    //        'document' => [
    //             'name' => 'Rerum quis temporibus eligendi.',
    //             'type' => 1,
    //             'content' => 'Ut ipsum dolorem rem vel quis rem occaecati.',
    //             '_csrf_token' => $form->getValues('_token')['document[_token]'],
    //        ],
    //        'file' => 'undefined',
    //     ]);

    //     $data = json_decode($this->client->getResponse()->getContent(), true);

    //     $this->assertSame('success', $data['alert']);
    // }

    public function testDeleteDocument()
    {
        $this->client->request('GET', $this->generateUri('document_delete', [
            'id' => $this->document->getId(),
        ]));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('delete', $data['action']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->dataFixtures = null;
    }
}
