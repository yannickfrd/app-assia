<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class NoteEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    public const CONTAINER_NOTES = '#container-notes';
    public const BUTTON_NEW_NOTE = 'button[data-action="new_note"]';
    public const MODAL_BUTTON_SAVE = 'button[data-action="save"]';
    public const MODAL_BUTTON_CLOSE = 'button[data-action="close"]';
    public const FORM_NOTE = 'form[name="note"]';

    public const MSG_FLASH = '#js-msg-flash';
    public const BUTTON_CLOSE_MSG = '#btn-close-msg';
    public const ALERT_SUCCESS = '.alert.alert-success';
    public const ALERT_WARNING = '.alert.alert-warning';
    public const ALERT_DANGER = '.alert.alert-danger';

    protected Client $client;

    protected function setUp(): void
    {
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testNote(): void
    {
        $this->client = $this->loginUser();

        $this->client->request('GET', '/support/1/show');

        $this->assertSelectorTextContains('h1', 'Suivi');

        $this->showNotesPage();
        $this->failToCreateNote();
        $this->createNote();
        $this->editNote();
        $this->deleteNoteByModal();
        $this->restoreCardNote();
        $this->deleteNoteByTable();
        $this->restoreTableNote();

        $this->client->quit();
    }

    private function showNotesPage(): void
    {
        $this->outputMsg('Show the notes page');

        $this->clickElement('#support-notes');

        $this->client->waitFor(self::CONTAINER_NOTES);
        $this->assertSelectorTextContains('h1', 'Notes sociales');
    }

    private function failToCreateNote(): void
    {
        $this->outputMsg('Fail to create a note');

        $this->clickElement(self::BUTTON_NEW_NOTE);

        $this->client->waitFor(self::FORM_NOTE);
        sleep(1);
        $this->client
            ->getCrawler()
            ->filter(self::FORM_NOTE)
            ->form([
                'note[title]' => $this->faker->sentence(mt_rand(5, 10), true),
            ])
        ;

        $this->clickElement(self::MODAL_BUTTON_SAVE);

        $this->client->waitFor(self::MSG_FLASH);
        $this->assertSelectorExists(self::MSG_FLASH.self::ALERT_DANGER);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
        $this->clickElement(self::MODAL_BUTTON_CLOSE);
    }

    private function createNote(): void
    {
        $this->outputMsg('Create a note');

        $this->clickElement(self::BUTTON_NEW_NOTE);

        $this->client->waitFor(self::FORM_NOTE);
        sleep(1);
        $this->client
            ->getCrawler()
            ->filter(self::FORM_NOTE)
            ->form([
                'note[title]' => $this->faker->sentence(mt_rand(5, 10), true),
                'note[editor]' => join('. ', $this->faker->paragraphs(mt_rand(1, 2))),
                'note[tags]' => [1, 2],
            ])
        ;

        $this->clickElement(self::MODAL_BUTTON_SAVE);

        $this->client->waitFor(self::MSG_FLASH);
        $this->assertSelectorExists(self::MSG_FLASH.self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
        $this->clickElement(self::MODAL_BUTTON_CLOSE);
    }

    private function editNote(): void
    {
        $this->outputMsg('Edit a note');

        $this->clickElement(self::CONTAINER_NOTES.' div[data-note-id]');

        $this->client->waitFor(self::FORM_NOTE);
        sleep(1);
        $this->client
            ->getCrawler()
            ->filter(self::FORM_NOTE)
            ->form([
                'note[title]' => $this->faker->sentence(mt_rand(5, 10), true),
                'note[editor]' => join('. ', $this->faker->paragraphs(mt_rand(1, 2))),
                'note[tags]' => [1, 2],
            ])
        ;

        $this->clickElement(self::MODAL_BUTTON_SAVE);

        $this->client->waitFor(self::MSG_FLASH);
        $this->assertSelectorExists(self::MSG_FLASH.self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
        $this->clickElement(self::MODAL_BUTTON_CLOSE);
    }

    private function deleteNoteByModal(): void
    {
        $this->outputMsg('Delete a note by modal');

        $this->clickElement(self::CONTAINER_NOTES.' div[data-note-id]');
        sleep(1);
        $this->client->waitFor('#confirm-modal');
        $this->clickElement('#modal-btn-delete');
        sleep(1);
        $this->clickElement('#modal-confirm-btn');

        $this->client->waitFor(self::MSG_FLASH);
        $this->assertSelectorExists(self::MSG_FLASH.self::ALERT_WARNING);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function deleteNoteByTable(): void
    {
        $this->outputMsg('Delete a note by table');

        $this->clickElement('a#table-view');
        $this->clickElement(self::CONTAINER_NOTES.' tbody>tr button[data-action="delete-note"]');
        sleep(1);
        $this->clickElement('#modal-confirm');

        $this->client->waitFor(self::MSG_FLASH.self::ALERT_WARNING);
        $this->assertSelectorExists(self::MSG_FLASH.self::ALERT_WARNING);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function restoreCardNote(): void
    {
        $this->outputMsg('Restore a note from card view');

        $this->clickElement('label[for="deleted_deleted"]');
        $this->clickElement('button[id="search"]');

        $this->client->waitFor(self::CONTAINER_NOTES);

        $this->restoreNote();
    }

    private function restoreTableNote(): void
    {
        $this->outputMsg('Restore a note from table view');

        $this->clickElement('label[for="deleted_deleted"]');
        $this->clickElement('button[id="search"]');

        $this->client->waitFor('table');

        $this->restoreNote();
    }

    private function restoreNote(): void
    {
        $this->clickElement('button[name="restore"]');

        $this->client->waitFor(self::MSG_FLASH);
        $this->assertSelectorExists(self::MSG_FLASH.self::ALERT_SUCCESS);
    }
}
