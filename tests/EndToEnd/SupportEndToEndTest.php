<?php

namespace App\Tests\EndToEnd;

use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class SupportEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    /** @var PantherClient */
    protected $client;

    protected function setUp()
    {
        $this->client = $this->createPantherLogin();

        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testSupport()
    {
        $this->outputMsg('Go to supports search page');

        /** @var Crawler */
        $crawler = $this->client->request('GET', '/supports');

        $this->assertSelectorTextContains('h1', 'Suivis');

        $this->outputMsg('Search a support');

        $form = $crawler->selectButton('search')->form([
            'fullname' => 'Doe',
        ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        $this->outputMsg('Select a support');

        $this->client->waitFor('table');
        $link = $crawler->filter('table tbody tr a.btn')->first()->link();

        $this->outputMsg('Go to a support view page');

        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Suivi social');

        $this->outputMsg('Go to a support edit page');

        $link = $crawler->filter('a#support_edit')->link();
        $crawler = $this->client->click($link);

        $this->outputMsg('Edit a support');

        $this->assertSelectorTextContains('h1', 'Édition du suivi');

        $form = $crawler->selectButton('send')->form([]);

        $crawler = $this->client->submit($form);

        $this->assertSelectorExists('.alert.alert-success');
    }
}
