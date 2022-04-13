<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class DocumentEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    protected Client $client;

    public function testDocument(): void
    {
        $this->client = $this->loginUser();

        /** @var Crawler */
        $crawler = $this->client->request('GET', '/support/1/show');

        $this->assertSelectorTextContains('h1', 'Suivi');

        $crawler = $this->goToSupportDocumentPage($crawler);
        $crawler = $this->addFile($crawler);
        $crawler = $this->editDocument($crawler);
        $crawler = $this->downloadDocument($crawler);
        // $crawler = $this->downloadAllDocuments($crawler);
        $crawler = $this->deleteDocumentInModal($crawler);
        $crawler = $this->deleteDocument($crawler);
    }

    private function goToSupportDocumentPage(Crawler $crawler): Crawler
    {
        $this->outputMsg('Show support documents page');

        $this->client->waitForVisibility('#support-documents');
        $link = $crawler->filter('#support-documents')->link();

        /** @var Crawler */
        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Documents');

        return $crawler;
    }

    private function addFile(Crawler $crawler)
    {
        $this->outputMsg('Add a new document');

        $this->client->waitForVisibility('#btn-new-files');
        $crawler->selectButton('btn-new-files')->click();
        sleep(1); //pop-up effect

        // $uploadedFile = new UploadedFile(
        //     dirname(__DIR__).'\fixtures\files\doc.docx',
        //     'doc.docx', null, 1, true
        // );

        $this->client->waitForVisibility('button[name="send"]');
        $form = $crawler->selectButton('send')->form([]);

        /** @var FormField $fileFormField */
        $fileFormField = $form['dropzone_document[files]'];
        $fileFormField->setValue(dirname(__DIR__).'/fixtures/files/doc.docx');

        $this->client->waitForVisibility('#dropzone ul li.list-group-item-success');
        $this->assertSelectorExists('#dropzone ul li.list-group-item-success');

        $crawler->selectButton('close')->click();
        sleep(1); //pop-up effect

        return $crawler;
    }

    private function editDocument(Crawler $crawler): Crawler
    {
        $this->outputMsg('Select a document');

        $this->client->waitForVisibility('td[data-document="name"]');
        $crawler->filter('td[data-document="name"]')->first()->click();
        sleep(1); //pop-up effect

        $this->outputMsg('Edit a document');

        $this->client->waitForVisibility('button[name="document_update"]');

        /** @var Crawler */
        $crawler = $this->client->submitForm('document_update', [
            'document[name]' => 'Document test',
            // 'document[type]' => [mt_rand(1, 9)],
            'document[content]' => 'Content test...',
        ]);

        $this->client->waitForVisibility('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        return $crawler;
    }

    private function downloadDocument(Crawler $crawler): Crawler
    {
        $this->outputMsg('Download a document');

        $this->client->waitForVisibility('tr>td>a');
        $crawler->filter('tr>td>a')->first()->click();

        return $crawler;
    }

    private function deleteDocument(Crawler $crawler): Crawler
    {
        $this->outputMsg('Delete a document');

        $this->client->waitForVisibility('button[data-action="delete"]');
        $crawler->filter('button[data-action="delete"]')->first()->click();
        sleep(1); //pop-up effect

        $this->client->waitForVisibility('#modal-confirm');
        $crawler->filter('#modal-confirm')->click();

        $this->client->waitForVisibility('#js-msg-flash.alert.alert-warning');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-warning');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1);

        return $crawler;
    }

    private function deleteDocumentInModal(Crawler $crawler): Crawler
    {
        $this->outputMsg('Delete a document in modal');

        $this->client->waitForVisibility('td[data-document="name"]');
        $crawler->filter('td[data-document="name"]')->first()->click();
        sleep(1); //pop-up effect

        $this->client->waitForVisibility('button[data-action="delete"]');
        $crawler->filter('button[data-url-document-delete]')->click();
        sleep(1); //pop-up effect

        $this->client->waitForVisibility('#modal-confirm');
        $crawler->filter('#modal-confirm')->click();
        sleep(1);

        $this->client->waitForVisibility('#js-msg-flash.alert.alert-warning');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-warning');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        return $crawler;
    }

    private function downloadAllDocuments(Crawler $crawler): Crawler
    {
        $this->outputMsg('Download all documents');

        $crawler->filter('#checkbox-all-files')->click();

        /** @var Crawler */
        $crawler = $this->client->submitForm('action-validate', [
            'action[type]' => 1,
        ]);

        $this->client->waitForVisibility('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        return $crawler;
    }
}
