<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class SupportEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    public const CONTAINER = '#table_supports';
    public const FIRST_BUTTON_SHOW = self::CONTAINER.' [data-action="show"]';
    public const FIRST_BUTTON_RESTORE = self::CONTAINER.' [data-action="restore"]';

    public const BUTTON_EDIT = 'a#support_edit';
    public const BUTTON_SAVE = '#send';
    public const BUTTON_DELETE = '#modal_delete_btn';

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

    public function testSupport(): void
    {
        $this->client = $this->loginUser();

        $this->outputMsg('Show supports search page');

        $this->client->request('GET', '/supports');

        $this->assertSelectorTextContains('h1', 'Suivis');

        $this->outputMsg('Search a support');

        $this->client->submitForm('search', [
            'fullname' => 'Doe',
        ]);

        $this->outputMsg('Select a support');

        $this->client->waitFor(self::CONTAINER);

        $this->outputMsg('Show a support view page');

        $this->clickElement(self::FIRST_BUTTON_SHOW);

        $this->assertSelectorTextContains('h1', 'Suivi social');

        $this->outputMsg('Show a support edit page');

        $this->clickElement(self::BUTTON_EDIT);

        $this->outputMsg('Edit a support');

        $this->fixScrollBehavior();
        sleep(1);

        $this->assertSelectorTextContains('h1', 'Édition');

        $this->clickElement(self::BUTTON_SAVE);

        $this->assertSelectorExists(self::ALERT_SUCCESS);
    }

    public function testDeleteAndRestoreSupport(): void
    {
        $this->client = $this->loginUser('user_super_admin');

        $this->outputMsg('Show supports search page');

        $this->client->request('GET', '/supports');

        $this->deleteSupport();

        $this->outputMsg('Show supports search page');

        $this->client->request('GET', '/supports');

        $this->restoreSupport();
    }

    private function deleteSupport(): void
    {
        $this->outputMsg('Select a support');

        $this->client->waitFor(self::CONTAINER);

        $this->outputMsg('Show a support view page');

        $this->clickElement(self::FIRST_BUTTON_SHOW);

        $this->assertSelectorTextContains('h1', 'Suivi social');

        $this->outputMsg('Delete a support');

        $this->clickElement(self::BUTTON_DELETE);

        $this->acceptWindowConfirm();
    }

    private function restoreSupport(): void
    {
        $this->outputMsg('Restore a support');

        $this->clickElement('label[for="deleted_deleted"]');
        $this->clickElement('button[id="search"]');

        $this->client->waitFor(self::CONTAINER);

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
