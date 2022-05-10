<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class DocumentEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    protected Client $client;

    public function testDocument(): void
    {
        $this->client = $this->loginUser();

        $this->client->request('GET', '/support/1/show');

        $this->assertSelectorTextContains('h1', 'Suivi');

        $this->goToSupportDocumentPage();
        $this->editDocument();
        $this->addFile();
        $this->editDocument();
        $this->downloadDocument();
        $this->deleteDocumentInModal();
        $this->deleteDocument();
        $this->restoreDocument();
        $this->downloadAllDocuments();

        $this->client->quit();
    }

    private function goToSupportDocumentPage(): void
    {
        $this->outputMsg('Show support documents page');

        $this->clickElement('#support-documents');

        $this->assertSelectorTextContains('h1', 'Documents');
    }

    private function addFile(): void
    {
        $this->outputMsg('Add a new document');

        $this->clickElement('#btn-new-files');

        $crawler = $this->client->waitForVisibility('#dropzone');
        $form = $crawler->filter('form[name="dropzone_document"]')->form();

        /** @var FormField $fileFormField */
        $fileFormField = $form['dropzone_document[files]'];
        $fileFormField->setValue(dirname(__DIR__).'/fixtures/files/doc.docx');

        $this->client->waitForVisibility('#dropzone ul li.list-group-item-success');
        $this->assertSelectorExists('#dropzone ul li.list-group-item-success');

        $this->clickElement('button[name="close"]');
    }

    private function editDocument(): void
    {
        $this->outputMsg('Select a document');

        $this->clickElement('td[data-cell="name"]');

        $this->outputMsg('Edit a document');

        $this->client->waitForVisibility('button[name="document_update"]');

        $this->client->submitForm('document_update', [
            'document[name]' => 'Document test',
            'document[tags]' => [1, 2],
            'document[content]' => 'Content test...',
        ]);

        $this->client->waitForVisibility('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $this->clickElement('#btn-close-msg');
    }

    private function downloadDocument(): void
    {
        $this->outputMsg('Download a document');

        $this->clickElement('tr>td>a');
    }

    private function downloadAllDocuments(): void
    {
        $this->outputMsg('Download all documents');

        $this->clickElement('#table-documents div.form-check');

        $this->client->submitForm('action-validate', [
            'action[type]' => 1,
        ]);

        $this->client->waitForVisibility('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $this->clickElement('#btn-close-msg');
    }

    private function deleteDocument(): void
    {
        $this->outputMsg('Delete a document');
        $this->clickElement('#container-documents button[data-action="delete"]');
        sleep(1);
        $this->clickElement('#modal-confirm');

        $this->client->waitForVisibility('#js-msg-flash.alert.alert-warning');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-warning');

        $this->clickElement('#btn-close-msg');
    }

    private function deleteDocumentInModal(): void
    {
        $this->outputMsg('Delete a document in modal');

        $this->clickElement('td[data-cell="name"]');
        sleep(1);
        $this->clickElement('#document-modal button[data-action="delete"]');
        sleep(1);
        $this->clickElement('#modal-confirm');

        $this->client->waitForVisibility('#js-msg-flash.alert.alert-warning');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-warning');

        $this->clickElement('#btn-close-msg');
    }

    private function restoreDocument(): void
    {
        $this->outputMsg('Restore a document');

        $this->clickElement('label[for="search_deleted_deleted"]');
        $this->clickElement('button[id="search"]');

        $this->client->waitForVisibility('table', 1);

        $this->clickElement('button[name="restore"]');

        $this->client->waitFor('#js-msg-flash', 3);
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $this->clickElement('button[name="restore"]');
        $this->clickElement('a#return_index');
    }
}
