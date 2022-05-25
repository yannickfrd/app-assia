<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class TaskEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    public const CONTAINER = '#container-tasks';

    public const BUTTON_NEW = '#js_new_task';
    public const BUTTON_SHOW = 'button[data-action="edit_task"]';
    public const BUTTON_DELETE = 'button[data-action="delete_task"]';
    public const BUTTON_RESTORE = 'button[name="restore"]';

    public const MODAL_BUTTON_SAVE = '#js-btn-save';
    public const MODAL_BUTTON_CLOSE = '#js-btn-cancel';
    public const FORM_TASK = 'form[name="task"]';

    public const MSG_FLASH = '#js-msg-flash';
    public const BUTTON_CLOSE_MSG = '#btn-close-msg';
    public const ALERT_SUCCESS = '.alert.alert-success';
    public const ALERT_WARNING = '.alert.alert-warning';

    protected Client $client;

    /** @var \Faker\Generator */
    protected $faker;

    protected function setUp(): void
    {
        $this->client = $this->loginUser();
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testTask(): void
    {
        $this->client->request('GET', '/support/1/show');

        $this->showTasksIndex();
        $this->createTask();
        $this->editTask();
        $this->toggleStatusTask();
        $this->deleteTaskByModal();
        $this->restoreTask();

        $this->client->quit();
    }

    private function showTasksIndex(): void
    {
        $this->outputMsg('Show tasks index page');

        $this->clickElement('#support-tasks');

        $this->assertSelectorTextContains('h1', 'Tâches');

        $this->fixScrollBehavior();
    }

    private function createTask(): void
    {
        $this->outputMsg('Create a task');

        $this->clickElement(self::BUTTON_NEW);
        sleep(1); //animation effect

        $this->setForm(self::FORM_TASK, [
            'task[title]' => $this->faker->sentence(mt_rand(5, 10), true),
            'task[_endDate]' => (new \DateTime())->modify('+1 week')->format('d/m/Y'),
            'task[_endTime]' => '16:00',
            'task[level]' => 3,
            'task[content]' => $this->faker->paragraphs(1),
            'task[tags]' => [1],
        ]);

        $this->clickElement('button[data-add-widget="alert"]');
        $this->clickElement(self::MODAL_BUTTON_SAVE);

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function editTask(): void
    {
        $this->outputMsg('Edit a task');

        $this->clickElement(self::BUTTON_SHOW);
        sleep(1); //animation effect

        $this->setForm(self::FORM_TASK, [
            'task[title]' => $this->faker->sentence(mt_rand(5, 10), true),
            'task[content]' => $this->faker->paragraphs(1),
            'task[tags]' => [1, 2],
        ]);

        $this->clickElement(self::MODAL_BUTTON_SAVE);

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function toggleStatusTask(): void
    {
        $this->outputMsg('Toggle status task');

        $this->clickElement('input[data-action="toggle_task_status"]');

        $this->client->waitFor(self::MSG_FLASH);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function deleteTaskByModal(): void
    {
        $this->outputMsg('Delete a task');

        $this->clickElement(self::BUTTON_SHOW);
        sleep(1); //animation effect

        $this->clickElement('#modal-btn-delete');
        sleep(1); //animation effect

        $this->clickElement('#modal-block #modal-confirm');

        $this->client->waitFor(self::ALERT_WARNING);
        $this->assertSelectorExists(self::ALERT_WARNING);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function restoreTask()
    {
        $this->outputMsg('Restore a task');

        $this->clickElement('button[type="reset"]');
        $this->clickElement('label[for="deleted_deleted"]');
        $this->clickElement('button[id="search"]');

        $this->client->waitFor('table');
        $this->clickElement(self::BUTTON_RESTORE);

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);
    }
}
