<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class NoteEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    protected Client $client;

    protected function setUp(): void
    {
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testNote(): void
    {
        $this->client = $this->loginUser();

        /** @var Crawler */
        $crawler = $this->client->request('GET', '/support/1/show');

        $this->assertSelectorTextContains('h1', 'Suivi');

        $crawler = $this->goToNotesPage($crawler);
        $crawler = $this->failToCreateNote($crawler);
//         $crawler = $this->createNote($crawler);
        $crawler = $this->editNote($crawler);
        $crawler = $this->deleteNote($crawler);
        $this->restoreCardNote($crawler);
        $this->restoreTableNote();

        $this->client->quit();
    }

    private function goToNotesPage(Crawler $crawler): Crawler
    {
        $this->outputMsg('Show the notes page');

        $link = $crawler->filter('#support-notes')->link();

        /** @var Crawler */
        $crawler = $this->client->click($link);

        $this->client->waitForVisibility('#container-notes');
        $this->assertSelectorTextContains('h1', 'Notes sociales');

        return $crawler;
    }

    private function failToCreateNote(Crawler $crawler): Crawler
    {
        $this->outputMsg('Fail to create a note');

        $form = $crawler->filter('button[data-action="new_note"]')->click();
        sleep(1); //pop-up effect

        $form = $crawler->filter('button[data-action="save"]')->form([
            'note[title]' => $this->faker->sentence(mt_rand(5, 10), true),
        ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        $this->client->waitForVisibility('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-danger');

        $crawler->selectButton('btn-close-msg')->click();
        $crawler->filter('button[data-action="close"]')->click();
        sleep(1);

        return $crawler;
    }

    private function createNote(Crawler $crawler): Crawler
    {
        $this->outputMsg('Create a note');

        $this->client->waitForVisibility('#js-new-note');
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

        $this->client->waitForVisibility('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        $crawler->filter('button[data-action="close"]')->click();
        sleep(1);

        return $crawler;
    }

    private function editNote(Crawler $crawler): Crawler
    {
        $this->outputMsg('Edit a note');

        $this->client->waitForVisibility('#container-notes div[data-note-id]', 1);
        sleep(1); //pop-up effect

        $this->client->waitForVisibility('#container-notes', 1);
        $crawler->filter('#container-notes div[data-note-id]')->eq(1)->click();
        sleep(1); //pop-up effect

        $this->client->waitForVisibility('button[data-action="save"]', 1);
        $form = $crawler->filter('button[data-action="save"]')->form([
            'note[title]' => $this->faker->sentence(mt_rand(5, 10), true),
            'note[editor]' => join('. ', $this->faker->paragraphs(mt_rand(1, 2))),
        ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        $this->client->waitFor('#js-msg-flash', 3);
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
//        sleep(5);
//        $crawler->filter('button[data-action="close"]')->click();
//        $this->outputMsg('__ okok __');

//        sleep(1);

        return $crawler;
    }

    private function deleteNote(Crawler $crawler): Crawler
    {
        $this->outputMsg('Delete a note');

        $this->client->waitForVisibility('#container-notes div[data-note-id]');
        $crawler->filter('#container-notes div[data-note-id]')->eq(1)->click();

        sleep(1); //pop-up effect

        $this->client->waitForVisibility('#modal-btn-delete');
        $crawler->filter('#modal-btn-delete')->click();

        sleep(1);
        $this->client->waitForVisibility('#confirm-modal');

        $crawler->filter('#modal-confirm-btn')->click();

        $this->client->waitForVisibility('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-warning');

        $crawler->selectButton('btn-close-msg')->click();
        // $crawler->filter('button[data-action="close"]')->click();

        sleep(1);

        return $crawler;
    }

    private function restoreCardNote(Crawler $crawler): void
    {
        $this->outputMsg('Restore a note from card view');

        $crawler->filter('label[for="deleted_deleted"]')->click();
        $crawler->filter('button[id="search"]')->click();

        $this->client->waitFor('#container-notes', 1);

        $this->restoreNote();
    }

    private function restoreTableNote(): void
    {
        $this->outputMsg('Restore a note from table view');

        $this->client->getWebDriver()->findElement(WebDriverBy::id('table-view'))->click();

        $this->clickElement('label[for="deleted_deleted"]');
        $this->clickElement('button[id="search"]');

        $this->client->waitFor('table', 1);

        $this->restoreNote();
    }

    private function restoreNote(): void
    {
        $this->client->getWebDriver()->findElement(WebDriverBy::name('restore'))->click();

        $this->client->waitFor('#js-msg-flash.alert', 3);
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');
    }
}
