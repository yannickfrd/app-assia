<?php

namespace App\Tests\EndToEnd;

use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class NoteEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    /** @var PantherClient */
    protected $client;

    protected function setUp(): void
    {
        $this->client = $this->createPantherLogin();

        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testNote()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', '/support/1/view');

        $this->assertSelectorTextContains('h1', 'Suivi');

        $crawler = $this->goToNotesPage($crawler);
        $crawler = $this->failToCreateNote($crawler);
        // $crawler = $this->createNote($crawler);
        $crawler = $this->editNote($crawler);
        $crawler = $this->deleteNote($crawler);
    }

    private function goToNotesPage(Crawler $crawler): Crawler
    {
        $this->outputMsg('Go to the notes page');

        $link = $crawler->filter('#support-notes')->link();

        /** @var Crawler */
        $crawler = $this->client->click($link);

        $this->client->waitFor('#container-notes');
        $this->assertSelectorTextContains('h1', 'Notes sociales');

        return $crawler;
    }

    private function failToCreateNote(Crawler $crawler): Crawler
    {
        $this->outputMsg('Fail to create a note');

        $form = $crawler->selectButton('js-new-note')->click();
        sleep(1); //pop-up effect

        $form = $crawler->filter('button[data-action="save"]')->form([
            'note[title]' => $this->faker->sentence(mt_rand(5, 10), true),
        ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-danger');

        $crawler->selectButton('btn-close-msg')->click();
        $crawler->filter('button[data-action="close"]')->click();
        sleep(2);

        return $crawler;
    }

    private function createNote(Crawler $crawler): Crawler
    {
        $this->outputMsg('Create a note');

        $form = $crawler->selectButton('js-new-note')->click();
        sleep(1); //pop-up effect

        $this->client->executeScript('document.getElementById("editor").click()');
        $this->client->getKeyboard()->sendKeys('test');
        $this->client->executeScript('navigator.clipboard.writeText("This text is now in the clipboard");');
        $this->client->executeScript('document.execCommand("paste")');
        $this->client->executeScript('document.getElementById("editor").innerHtml = "test"');

        $form = $crawler->filter('button[data-action="save"]')->form([
            'note[title]' => $this->faker->sentence(mt_rand(5, 10), true),
            'note[editor]' => join('. ', $this->faker->paragraphs(mt_rand(1, 2))),
        ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        $crawler->filter('button[data-action="close"]')->click();
        sleep(1);

        return $crawler;
    }

    private function editNote(Crawler $crawler): Crawler
    {
        $this->outputMsg('Edit a note');

        $this->client->waitFor('#container-notes div[data-note-id]');
        $crawler->filter('#container-notes div[data-note-id]')->eq(1)->click();
        sleep(1); //pop-up effect

        $this->client->waitFor('button[data-action="save"]');
        $form = $crawler->filter('button[data-action="save"]')->form([
            'note[title]' => $this->faker->sentence(mt_rand(5, 10), true),
            'note[editor]' => join('. ', $this->faker->paragraphs(mt_rand(1, 2))),
        ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        $crawler->filter('button[data-action="close"]')->click();

        sleep(1);

        return $crawler;
    }

    private function deleteNote(Crawler $crawler): Crawler
    {
        $this->outputMsg('Delete a note');

        $this->client->waitFor('#container-notes div[data-note-id]');
        $crawler->filter('#container-notes div[data-note-id]')->eq(1)->click();

        sleep(1); //pop-up effect

        $this->client->waitFor('#modal-btn-delete');
        $crawler->filter('#modal-btn-delete')->click();

        $this->client->waitFor('#confirm-modal');
        $crawler->filter('#modal-confirm-btn')->click();

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-warning');

        $crawler->selectButton('btn-close-msg')->click();
        // $crawler->filter('button[data-action="close"]')->click();

        sleep(1);

        return $crawler;
    }
}
