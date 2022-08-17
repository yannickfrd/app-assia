<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class DocumentEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    public const BUTTON_NEW = '#btn_add_files';

    public const CONTAINER = '#container_documents';
    public const FIRST_BUTTON_SHOW = self::CONTAINER.' [data-action="show"]';
    public const FIRST_BUTTON_PREVIEW = self::CONTAINER.' button[data-action="preview"]';
    public const FIRST_BUTTON_DOWNLOAD = self::CONTAINER.' button[data-action="download"]';
    public const FIRST_BUTTON_DELETE = self::CONTAINER.' button[data-action="delete"]';
    public const FIRST_BUTTON_RESTORE = self::CONTAINER.' button[data-action="restore"]';

    public const MODAL = '#modal_document';
    public const FORM = 'form[name="document"]';
    public const MODAL_BUTTON_SAVE = self::MODAL.' button[data-action="save"]';
    public const MODAL_BUTTON_CLOSE = self::MODAL.' button[type="button" data-dismiss="modal"]';
    public const MODAL_BUTTON_DELETE = self::MODAL.' button[data-action="delete"]';

    public const MODAL_BUTTON_CONFIRM = '#modal_confirm_btn';

    public const ALERT_SUCCESS = '.toast.show.alert-success';
    public const ALERT_WARNING = '.toast.show.alert-warning';
    public const BUTTON_CLOSE_MSG = '.toast.show .btn-close';

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
        $this->previewDocument();
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

        $this->clickElement('#modal_dropzone button[name="close"]');
    }

    private function editDocument(): void
    {
        $this->outputMsg('Select a document');

        $this->clickElement(self::FIRST_BUTTON_SHOW);

        $this->outputMsg('Edit a document');

        $this->client->waitFor(self::MODAL_BUTTON_SAVE);
        sleep(2); // animation effect

        $this->setForm(self::FORM, [
            'document[name]' => $this->faker->words(mt_rand(3, 5), true),
            'document[tags]' => [1, 2],
            'document[content]' => $this->faker->sentence(),
        ]);

        $this->clickElement(self::MODAL_BUTTON_SAVE);

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function previewDocument(): void
    {
        $this->outputMsg('Preview a document');
        sleep(1);

        $this->clickElement(self::FIRST_BUTTON_PREVIEW);
    }

    private function downloadDocument(): void
    {
        $this->outputMsg('Download a document');

        $this->clickElement(self::FIRST_BUTTON_DOWNLOAD);
        sleep(2);
    }

    private function downloadAllDocuments(): void
    {
        $this->outputMsg('Download all documents');

        $this->clickElement('#table_documents div.form-check');

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
        $this->clickElement(self::FIRST_BUTTON_DELETE);
        sleep(1); // animation effect
        $this->clickElement(self::MODAL_BUTTON_CONFIRM);

        $this->client->waitFor(self::ALERT_WARNING);
        $this->assertSelectorExists(self::ALERT_WARNING);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function deleteDocumentInModal(): void
    {
        $this->outputMsg('Delete a document in modal');

        $this->clickElement(self::FIRST_BUTTON_SHOW);
        sleep(2); // animation effect
        $this->clickElement(self::MODAL_BUTTON_DELETE);
        sleep(1); // animation effect
        $this->clickElement(self::MODAL_BUTTON_CONFIRM);

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

        $this->clickElement(self::FIRST_BUTTON_RESTORE);

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::FIRST_BUTTON_RESTORE);
        $this->clickElement('a#return_index');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client->quit();
    }
}
