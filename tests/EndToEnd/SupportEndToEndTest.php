<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class SupportEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

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

        $this->client->waitFor('table');

        $this->outputMsg('Show a support view page');

        $this->clickElement('table tbody tr a.btn');

        $this->assertSelectorTextContains('h1', 'Suivi social');

        $this->outputMsg('Show a support edit page');

        $this->clickElement('a#support_edit');

        $this->fixScrollBehavior();
        sleep(1);

        $this->outputMsg('Edit a support');

        $this->assertSelectorTextContains('h1', 'Édition');

        $this->clickElement('#send');

        $this->assertSelectorExists('.toast.alert-success');
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

        $this->client->waitFor('table');

        $this->outputMsg('Show a support view page');

        $this->clickElement('table tbody tr a.btn');

        $this->assertSelectorTextContains('h1', 'Suivi social');

        $this->outputMsg('Delete a support');

        $this->clickElement('a#modal-btn-delete');

        $this->acceptWindowConfirm();
    }

    private function restoreSupport(): void
    {
        $this->outputMsg('Restore a support');

        $this->clickElement('label[for="deleted_deleted"]');
        $this->clickElement('button[id="search"]');

        $this->client->waitFor('table');

        $this->clickElement('a[title="Restaurer"]');

        $this->client->waitFor('.toast.alert-success');
        $this->assertSelectorExists('.toast.alert-success');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client->quit();
    }
}
