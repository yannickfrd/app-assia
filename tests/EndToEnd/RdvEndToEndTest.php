<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class RdvEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    public const BUTTON_NEW = '#js-new-rdv';
    public const BUTTON_SHOW = 'a.calendar-event';
    public const BUTTON_DELETE = 'button[data-action="delete-rdv"]';
    public const BUTTON_RESTORE = 'button[name="restore"]';

    public const MODAL_BUTTON_SAVE = 'button[name="save-rdv"]';
    public const MODAL_BUTTON_CLOSE = '#js-btn-cancel';
    public const FORM_RDV = 'form[name="rdv"]';

    public const ALERT_SUCCESS = '.toast.alert-success';
    public const ALERT_WARNING = '.toast.alert-warning';
    public const BUTTON_CLOSE_MSG = '.toast .btn-close';

    protected Client $client;

    /** @var \Faker\Generator */
    protected $faker;

    protected function setUp(): void
    {
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testRdv(): void
    {
        $this->client = $this->loginUser('john_user');

        $this->client->request('GET', '/support/1/show');

        $this->showCalendar();
        $this->createRdv();
        $this->editRdv();
        $this->deleteRdvByModal();
        $this->deleteRdvByTable();
    }

    public function testRestoreRdv(): void
    {
        $this->client = $this->loginUser('user_super_admin');

        $this->client->request('GET', '/support/1/rdvs');

        $this->restoreRdv();
    }

    private function showCalendar(): void
    {
        $this->outputMsg('Show calendar page');

        $this->clickElement('#support-calendar');

        $this->assertSelectorTextContains('h1', 'Rendez-vous');

        $this->fixScrollBehavior();
        $this->clickElement('#show-weekend');
    }

    private function createRdv(): void
    {
        $this->outputMsg('Create a rdv');

        $this->clickElement(self::BUTTON_NEW);

        $this->client->waitFor(self::MODAL_BUTTON_SAVE);
        sleep(1); // animation effect

        $this->setForm(self::FORM_RDV, [
            'rdv[title]' => $this->faker->sentence(mt_rand(5, 10), true),
            'start' => '10:30',
            'end' => '12:30',
            'rdv[tags]' => [1],
            'rdv[content]' => join('. ', $this->faker->paragraphs(mt_rand(1, 2))),
        ]);

        $this->clickElement(self::MODAL_BUTTON_SAVE);

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function editRdv(): void
    {
        $this->outputMsg('Edit a rdv');

        $this->clickElement(self::BUTTON_SHOW);

        $this->client->waitFor(self::MODAL_BUTTON_SAVE);
        sleep(1); // animation effect

        $this->setForm(self::FORM_RDV, [
            'rdv[title]' => $this->faker->sentence(mt_rand(5, 10), true),
            'rdv[tags]' => [1, 2],
            'rdv[content]' => join('. ', $this->faker->paragraphs(mt_rand(1, 2))),
            'rdv[users]' => [1],
        ]);

        $this->clickElement(self::MODAL_BUTTON_SAVE);

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function deleteRdvByModal(): void
    {
        $this->outputMsg('Delete a rdv by modal');

        $this->clickElement(self::BUTTON_SHOW);
        sleep(1); // animation effect

        $this->clickElement('#modal-btn-delete');
        sleep(1); // animation effect

        $this->clickElement('#modal-block #modal-confirm');

        $this->client->waitFor(self::ALERT_WARNING);
        $this->assertSelectorExists(self::ALERT_WARNING);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function deleteRdvByTable(): void
    {
        $this->outputMsg('Delete a rdv by table');

        $this->clickElement('a[data-original-title="Passer en vue liste"]');
        $this->clickElement(self::BUTTON_DELETE);
        sleep(1); // animation effect

        $this->clickElement('#modal-block #modal-confirm');

        $this->client->waitFor(self::ALERT_WARNING);
        $this->assertSelectorExists(self::ALERT_WARNING);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function restoreRdv(): void
    {
        $this->outputMsg('Restore a rdv');

        $this->clickElement('label[for="deleted_deleted"]');
        $this->clickElement('button[id="search"]');

        $this->client->waitFor('table');
        $this->clickElement(self::BUTTON_RESTORE);

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client->quit();
    }
}
