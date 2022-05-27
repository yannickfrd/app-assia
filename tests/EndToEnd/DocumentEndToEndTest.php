<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class DocumentEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    public const BUTTON_NEW = '#btn-new-files';
    public const BUTTON_SHOW = 'td[data-cell="name"]';
    public const BUTTON_DELETE = 'button[data-action="delete"]';
    public const BUTTON_RESTORE = 'button[name="restore"]';

    public const MODAL_BUTTON_SAVE = 'button[name="document_update"]';
    public const MODAL_BUTTON_CLOSE = 'button[type="button" data-dismiss="modal"]';
    public const FORM_DOCUMENT = 'form[name="document"]';

    public const MSG_FLASH = '#js-msg-flash';
    public const BUTTON_CLOSE_MSG = '#btn-close-msg';
    public const ALERT_SUCCESS = '.alert.alert-success';
    public const ALERT_WARNING = '.alert.alert-warning';

    protected Client $client;

    /** @var \Faker\Generator */
    protected $faker;

    protected function setUp(): void
    {
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testDocument(): void
    {
        $this->client = $this->loginUser('john_user');

        $this->client->request('GET', '/support/1/show');

        $this->goToSupportDocumentPage();
        $this->editDocument();
        $this->addFile();
        $this->editDocument();
        $this->downloadDocument();
        $this->deleteDocumentInModal();
        $this->deleteDocument();
        $this->downloadAllDocuments();
    }

    public function testRestoreRdv(): void
    {
        $this->client = $this->loginUser('user_super_admin');

        $this->client->request('GET', '/support/1/documents');

        $this->restoreDocument();
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

        $this->clickElement(self::BUTTON_NEW);

        $crawler = $this->client->waitFor('#dropzone');
        $form = $crawler->filter('form[name="dropzone_document"]')->form();

        /** @var FormField $fileFormField */
        $fileFormField = $form['dropzone_document[files]'];
        $fileFormField->setValue(dirname(__DIR__).'/fixtures/files/doc.docx');

        $this->client->waitFor('#dropzone ul li.list-group-item-success');
        $this->assertSelectorExists('#dropzone ul li.list-group-item-success');

        $this->clickElement('button[name="close"]');
    }

    private function editDocument(): void
    {
        $this->outputMsg('Select a document');

        $this->clickElement(self::BUTTON_SHOW);

        $this->outputMsg('Edit a document');

        $this->client->waitFor(self::MODAL_BUTTON_SAVE);
        sleep(2); // animation effect

        $this->setForm(self::FORM_DOCUMENT, [
            'document[name]' => $this->faker->words(mt_rand(3, 5), true),
            'document[tags]' => [1, 2],
            'document[content]' => $this->faker->sentence(),
        ]);

        $this->clickElement(self::MODAL_BUTTON_SAVE);

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
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

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function deleteDocument(): void
    {
        $this->outputMsg('Delete a document');
        $this->clickElement('#container-documents button[data-action="delete"]');
        sleep(1); // animation effect
        $this->clickElement('#modal-confirm');

        $this->client->waitFor(self::ALERT_WARNING);
        $this->assertSelectorExists(self::ALERT_WARNING);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function deleteDocumentInModal(): void
    {
        $this->outputMsg('Delete a document in modal');

        $this->clickElement(self::BUTTON_SHOW);
        sleep(2); // animation effect
        $this->clickElement('#document-modal button[data-action="delete"]');
        sleep(1); // animation effect
        $this->clickElement('#modal-confirm');

        $this->client->waitFor(self::ALERT_WARNING);
        $this->assertSelectorExists(self::ALERT_WARNING);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function restoreDocument(): void
    {
        $this->outputMsg('Restore a document');

        $this->clickElement('label[for="search_deleted_deleted"]');
        $this->clickElement('button[id="search"]');

        $this->client->waitFor('table', 1);

        $this->clickElement(self::BUTTON_RESTORE);

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_RESTORE);
        $this->clickElement('a#return_index');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client->quit();
    }
}
