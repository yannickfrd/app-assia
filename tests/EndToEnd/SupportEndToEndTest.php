<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class SupportEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    /** @var PantherClient */
    protected $client;

    protected function setUp(): void
    {
        $this->faker = \Faker\Factory::create('fr_FR');
    }

//    public function testSupport(): void
//    {
//        $this->client = $this->loginUser();
//
//        $this->outputMsg('Show supports search page');
//
//        /** @var Crawler */
//        $crawler = $this->client->request('GET', '/supports');
//
//        $this->assertSelectorTextContains('h1', 'Suivis');
//
//        $this->outputMsg('Search a support');
//
//        /** @var Crawler */
//        $crawler = $this->client->submitForm('search', [
//            'fullname' => 'Doe',
//        ]);
//
//        $this->outputMsg('Select a support');
//
//        $this->client->waitForVisibility('table');
//        $link = $crawler->filter('table tbody tr a.btn')->first()->link();
//
//        $this->outputMsg('Show a support view page');
//
//        $crawler = $this->client->click($link);
//
//        $this->assertSelectorTextContains('h1', 'Suivi social');
//
//        $this->outputMsg('Show a support edit page');
//
//        $link = $crawler->filter('a#support_edit')->link();
//        $crawler = $this->client->click($link);
//
//        $this->outputMsg('Edit a support');
//
//        $this->assertSelectorTextContains('h1', 'Édition du suivi');
//
//        $crawler = $this->client->submitForm('send2');
//
//        $this->assertSelectorExists('.alert.alert-success');
//
//        $this->client->quit();
//    }

    public function testDeleteSupport(): void
    {
        $this->client = $this->loginUser('r.admin');

        $this->outputMsg('Show supports search page');

        /** @var Crawler */
        $crawler = $this->client->request('GET', '/supports');

        $this->outputMsg('Select a support');

        $this->client->waitForVisibility('table');
        $link = $crawler->filter('table tbody tr a.btn')->first()->link();

        $this->outputMsg('Show a support view page');

        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Suivi social');

        $this->outputMsg('Delete a support');

        $this->client->waitForVisibility('a#modal-btn-delete');
        $crawler->filter('a#modal-btn-delete')->click();

        sleep(5);

        $crawler = $this->client->clickLink('OK');

        /** @var Crawler */
        $crawler = $this->client->request('GET', '/supports', [
            'deleted' => ['deleted' => true]
        ]);

        sleep(5);

        $this->client->waitForVisibility('table');
        $link = $crawler->filter('table tbody tr a.btn')->first()->link();

    }
}
