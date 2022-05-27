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

    public const NOTES_TABLE = '#table-notes';
    public const BUTTON_SHOW_NOTE = '#table-notes a[data-action="show"]';

    public const MSG_FLASH = '#js-msg-flash';
    public const BUTTON_CLOSE_MSG = '#btn-close-msg';
    public const ALERT_SUCCESS = '.alert.alert-success';
    public const ALERT_WARNING = '.alert.alert-warning';
    public const ALERT_DANGER = '.alert.alert-danger';

    protected Client $client;

    /** @var \Faker\Generator */
    protected $faker;

    protected function setUp(): void
    {
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testNote(): void
    {
        $this->client = $this->loginUser('john_user');

        $this->client->request('GET', '/support/1/show');

        $this->showNotesPage();

        $this->failToCreateNote();
        $this->createNote();
        $this->editNote();
        $this->deleteNoteByModal();

        $this->showNotesByTableView();
        $this->createNoteByTableView();
        $this->editNoteByTableView();
        $this->deleteNoteByTableView();
    }

    public function testRestoreNote(): void
    {
        $this->client = $this->loginUser('user_super_admin');

        $this->client->request('GET', '/support/1/notes/card-view');

        $this->restoreNoteByCardsView();

        $this->client->request('GET', '/support/1/notes/table-view');

        $this->restoreNoteByTableView();
    }

    private function showNotesPage(): void
    {
        $this->outputMsg('Show the notes page');

        $this->clickElement('#support-notes');

        $this->client->waitFor(self::CONTAINER_NOTES);
        $this->assertSelectorTextContains('h1', 'Notes sociales');
    }

    private function showNotesByTableView(): void
    {
        $this->outputMsg('Show the notes by table-view');

        $this->clickElement('a#table-view');

        $this->client->waitFor(self::CONTAINER_NOTES.'.table-responsive');
        $this->assertSelectorTextContains('h1', 'Notes sociales');
    }

    private function failToCreateNote(): void
    {
        $this->outputMsg('Fail to create a note');

        $this->clickElement(self::BUTTON_NEW_NOTE);
        sleep(1); // transition delay

        $this->setForm(self::FORM_NOTE, [
            'note[title]' => $this->faker->sentence(mt_rand(5, 10), true),
        ]);

        $this->clickElement(self::MODAL_BUTTON_SAVE);

        $this->client->waitFor(self::ALERT_DANGER);
        $this->assertSelectorExists(self::ALERT_DANGER);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
        $this->clickElement(self::MODAL_BUTTON_CLOSE);
    }

    private function createNote(): void
    {
        $this->outputMsg('Create a note by card-view');

        $this->clickElement(self::BUTTON_NEW_NOTE);

        $this->setNoteForm();

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
        $this->clickElement(self::MODAL_BUTTON_CLOSE);
    }

    private function editNote(): void
    {
        $this->outputMsg('Edit a note by card-view');

        $this->clickElement(self::CONTAINER_NOTES.' div[data-note-id]');

        $this->setNoteForm();

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
        $this->clickElement(self::MODAL_BUTTON_CLOSE);
    }

    private function deleteNoteByModal(): void
    {
        $this->outputMsg('Delete a note by modal');

        $this->clickElement(self::CONTAINER_NOTES.' div[data-note-id]');
        sleep(1); // transition delay
        $this->client->waitFor('#confirm-modal');
        $this->clickElement('#modal-btn-delete');
        sleep(1); // transition delay
        $this->clickElement('#modal-confirm-btn');

        $this->client->waitFor(self::ALERT_WARNING);
        $this->assertSelectorExists(self::ALERT_WARNING);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function createNoteByTableView(): void
    {
        $this->outputMsg('Create a note by table-view');

        $this->clickElement(self::BUTTON_NEW_NOTE);

        $this->setNoteForm();

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
        $this->clickElement(self::MODAL_BUTTON_CLOSE);
    }

    private function editNoteByTableView(): void
    {
        $this->outputMsg('Edit a note by table-view');

        $this->clickElement(self::BUTTON_SHOW_NOTE);

        $this->setNoteForm();

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
        $this->clickElement(self::MODAL_BUTTON_CLOSE);
    }

    private function restoreNoteByCardsView(): void
    {
        $this->outputMsg('Restore a note from card-view');

        $this->clickElement('label[for="deleted_deleted"]');
        $this->clickElement('button[id="search"]');

        $this->client->waitFor(self::CONTAINER_NOTES);

        $this->restoreNote();
    }

    private function deleteNoteByTableView(): void
    {
        $this->outputMsg('Delete a note by table-view');

        $this->clickElement(self::CONTAINER_NOTES.' tbody>tr button[data-action="delete-note"]');
        sleep(1); // transition delay
        $this->clickElement('#modal-confirm');

        $this->client->waitFor(self::ALERT_WARNING);
        $this->assertSelectorExists(self::ALERT_WARNING);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function restoreNoteByTableView(): void
    {
        $this->outputMsg('Restore a note from table-view');

        $this->clickElement('label[for="deleted_deleted"]');
        $this->clickElement('button[id="search"]');

        $this->client->waitFor(self::NOTES_TABLE);

        $this->restoreNote();
    }

    private function restoreNote(): void
    {
        sleep(1); // transition delay
        $this->clickElement('button[name="restore"]');

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);
    }

    private function setNoteForm(): void
    {
        sleep(1); // transition delay

        $this->setForm(self::FORM_NOTE, [
            'note[title]' => $this->faker->sentence(mt_rand(5, 10), true),
            'note[editor]' => join('. ', $this->faker->paragraphs(mt_rand(1, 2))),
            'note[tags]' => [1, 2],
        ]);

        $this->clickElement(self::MODAL_BUTTON_SAVE);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client->quit();
    }
}
