<?php

namespace App\Tests\EndToEnd;

use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class DocumentEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    /** @var PantherClient */
    protected $client;

    protected function setUp()
    {
        $this->client = $this->createPantherLogin();

        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testDocument()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', '/support/1/view');

        $this->assertSelectorTextContains('h1', 'Suivi');

        $crawler = $this->goToSupportDocumentPage($crawler);
        $crawler = $this->deleteDocumentInModal($crawler);
        $crawler = $this->addFile($crawler);
        $crawler = $this->editDocument($crawler);
        $crawler = $this->downloadDocument($crawler);
        $crawler = $this->downloadAllDocuments($crawler);
        $crawler = $this->deleteDocument($crawler);
    }

    private function goToSupportDocumentPage(Crawler $crawler): Crawler
    {
        $this->outputMsg('Go to support documents page');

        $this->client->waitFor('#support-documents');
        $link = $crawler->filter('#support-documents')->link();

        /** @var Crawler */
        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Documents');

        return $crawler;
    }

    private function addFile(Crawler $crawler)
    {
        $this->outputMsg('Add a new document');

        $this->client->waitFor('#btn-new-files');
        $crawler->selectButton('btn-new-files')->click();
        sleep(1); //pop-up effect

        // $uploadedFile = new UploadedFile(
        //     dirname(__DIR__).'\DataFixturesTest\files\doc.docx',
        //     'doc.docx', null, 1, true
        // );

        $this->client->waitFor('button[name="send"]');
        $form = $crawler->selectButton('send')->form([]);

        /** @var FormField $fileFormField */
        $fileFormField = $form['dropzone_document[files]'];
        $fileFormField->setValue(dirname(__DIR__).'/DataFixturesTest/files/doc.docx');

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        $crawler->selectButton('close')->click();
        sleep(1); //pop-up effect

        return $crawler;
    }

    private function editDocument(Crawler $crawler): Crawler
    {
        $this->outputMsg('Select a document');

        $this->client->waitFor('td[data-document="name"]');
        $crawler->filter('td[data-document="name"]')->first()->click();
        sleep(1); //pop-up effect

        $this->outputMsg('Edit a document');

        $this->client->waitFor('button[name="document_update"]');
        $form = $crawler->selectButton('document_update')->form([
            'document[name]' => $this->faker->sentence(mt_rand(3, 5), true),
            'document[type]' => mt_rand(1, 9),
            'document[content]' => join('. ', $this->faker->paragraphs(1)),
        ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        return $crawler;
    }

    private function downloadDocument(Crawler $crawler): Crawler
    {
        $this->outputMsg('Download a document');

        $this->client->waitFor('tr>td>a');
        $crawler->filter('tr>td>a')->first()->click();

        return $crawler;
    }

    private function deleteDocument(Crawler $crawler): Crawler
    {
        $this->outputMsg('Delete a document');

        $this->client->waitFor('button[data-action="delete"]');
        $crawler->filter('button[data-action="delete"]')->first()->click();
        sleep(1); //pop-up effect

        $this->client->waitFor('#modal-confirm');
        $crawler->filter('#modal-confirm')->click();

        $this->client->waitFor('#js-msg-flash.alert.alert-warning');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-warning');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1);

        return $crawler;
    }

    private function deleteDocumentInModal(Crawler $crawler): Crawler
    {
        $this->outputMsg('Delete a document in modal');

        $this->client->waitFor('td[data-document="name"]');
        $crawler->filter('td[data-document="name"]')->first()->click();
        sleep(1); //pop-up effect

        $this->client->waitFor('button[data-action="delete"]');
        $crawler->filter('button[data-url-document-delete]')->click();
        sleep(1); //pop-up effect

        $this->client->waitFor('#modal-confirm');
        $crawler->filter('#modal-confirm')->click();
        sleep(1);

        $this->client->waitFor('#js-msg-flash.alert.alert-warning');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-warning');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        return $crawler;
    }

    private function downloadAllDocuments(Crawler $crawler): Crawler
    {
        $this->outputMsg('Download all documents');

        $crawler->filter('#checkbox-all-files')->click();

        $form = $crawler->selectButton('action-validate')->form([
            'action[type]' => 1,
        ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        return $crawler;
    }
}
