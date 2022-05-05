<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class SupportEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    protected Client $client;

    protected function setUp(): void
    {
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testSupport(): void
    {
        $this->client = $this->loginUser();

        $this->outputMsg('Show supports search page');

        /* @var Crawler */
        $this->client->request('GET', '/supports');

        $this->assertSelectorTextContains('h1', 'Suivis');

        $this->outputMsg('Search a support');

        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [
            'fullname' => 'Doe',
        ]);

        $this->outputMsg('Select a support');

        $this->client->waitForVisibility('table');
        $link = $crawler->filter('table tbody tr a.btn')->first()->link();

        $this->outputMsg('Show a support view page');

        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Suivi social');

        $this->outputMsg('Show a support edit page');

        $link = $crawler->filter('a#support_edit')->link();
        $this->client->click($link);

        $this->outputMsg('Edit a support');

        $this->assertSelectorTextContains('h1', 'Édition du suivi');

        $this->client->submitForm('send2');

        $this->assertSelectorExists('.alert.alert-success');

        $this->client->quit();
    }

    public function testDeleteAndRestoreSupport(): void
    {
        $this->client = $this->loginUser('r.admin');

        $this->outputMsg('Show supports search page');
        /** @var Crawler */
        $crawler = $this->client->request('GET', '/supports');

        $this->deleteSupport($crawler);

        $this->outputMsg('Show supports search page');
        /** @var Crawler */
        $crawler = $this->client->request('GET', '/supports');

        $this->restoreSupport();

        $this->client->quit();
    }

    private function deleteSupport(Crawler $crawler): void
    {
        $this->outputMsg('Select a support');

        $this->client->waitForVisibility('table');
        $link = $crawler->filter('table tbody tr a.btn')->first()->link();

        $this->outputMsg('Show a support view page');

        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Suivi social');

        $this->outputMsg('Delete a support');

        $this->client->waitForVisibility('a#modal-btn-delete');
        $crawler->filter('a#modal-btn-delete')->first()->click();

        $this->acceptWindowConfirm();
    }

    private function restoreSupport(): void
    {
        $this->outputMsg('Restore a support');

        $this->clickElement('label[for="deleted_deleted"]');
        $this->clickElement('button[id="search"]');

        $this->client->waitForVisibility('table', 1);

        $this->client->getWebDriver()->findElement(WebDriverBy::name('restore'))->click();

        $this->client->waitFor('.alert', 45);
        $this->assertSelectorExists('.alert.alert-success');
    }
}
