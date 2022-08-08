<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class TaskEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    public const CONTAINER = '#container-tasks';

    public const BUTTON_NEW = 'button[data-action="new_task"]';
    public const FIRST_BUTTON_SHOW = 'button[data-action="edit"]';
    public const FIRST_BUTTON_DELETE = 'button[data-action="delete"]';
    public const FIRST_BUTTON_RESTORE = 'tr button[data-action="restore"]';
    public const FIRST_CHECKBOX_TOGGLE_STATUS = 'input[data-action="toggle_status"]';

    public const FORM_TASK = 'form[name="task"]';
    public const MODAL_BUTTON_SAVE = '#modal_task button[data-action="save"]';
    public const MODAL_BUTTON_CLOSE = '#modal_task button[data-action="close_modal"]';
    public const MODAL_BUTTON_DELETE = '#modal_task button[data-action="delete"]';

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

    public function testTask(): void
    {
        $this->client = $this->loginUser('john_user');

        $this->client->request('GET', '/support/1/show');

        $this->showTasksIndex();
        $this->createTask();
        $this->editTask();
        $this->toggleStatusTask();
        $this->deleteTaskByModal();
    }

    public function testRestoreTask(): void
    {
        $this->client = $this->loginUser('user_super_admin');

        $this->client->request('GET', '/support/1/tasks');

        $this->restoreTask();
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
        sleep(1); // animation effect

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

        $this->clickElement(self::FIRST_BUTTON_SHOW);
        sleep(1); // animation effect

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

        $this->clickElement(self::FIRST_CHECKBOX_TOGGLE_STATUS);
        sleep(1); // animation effect

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function deleteTaskByModal(): void
    {
        $this->outputMsg('Delete a task');

        $this->clickElement(self::FIRST_BUTTON_SHOW);
        sleep(1); // animation effect

        $this->clickElement(self::MODAL_BUTTON_DELETE);
        sleep(1); // animation effect

        $this->clickElement(self::MODAL_BUTTON_CONFIRM);

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
        $this->clickElement(self::FIRST_BUTTON_RESTORE);

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client->quit();
    }
}
