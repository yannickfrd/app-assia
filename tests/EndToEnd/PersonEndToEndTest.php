<?php

namespace App\Tests\EndToEnd;

use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class PersonEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    /** @var PantherClient */
    protected $client;

    protected function setUp()
    {
        $this->client = $this->createPantherLogin();
    }

    public function testPerson()
    {
        $this->outputMsg('Go to people search page');

        /** @var Crawler */
        $crawler = $this->client->request('GET', '/people');

        $this->assertSelectorTextContains('h1', 'Rechercher une personne');

        $crawler = $this->searchPerson($crawler);
        $crawler = $this->editPerson($crawler);
        $crawler = $this->createNewGroupForPerson($crawler);
    }

    private function searchPerson(Crawler $crawler): Crawler
    {
        $this->outputMsg('Search a person');

        $crawler->selectButton('search')->form([
            'firstname' => 'John',
            'lastname' => 'DOE',
        ]);

        $this->client->waitFor('a.btn[title="Voir la fiche de la personne"]');
        $link = $crawler->filter('a.btn[title="Voir la fiche de la personne"]')->link();

        $this->outputMsg('Go to person page');

        /** @var Crawler */
        $crawler = $this->client->click($link);

        return $crawler;
    }

    private function editPerson(Crawler $crawler): Crawler
    {
        $this->outputMsg('Edit person');

        $this->client->waitFor('#updatePerson');
        $crawler->selectButton('updatePerson')->click();

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();

        return $crawler;
    }

    private function createNewGroupForPerson(Crawler $crawler): Crawler
    {
        $this->outputMsg('Create a new group for the person');

        $crawler->selectButton('btn-new-group')->click();
        sleep(1);

        $this->client->waitFor('#js-btn-confirm');
            
        $crawler = $this->client->submitForm('js-btn-confirm', [
            'person_new_group[peopleGroup][familyTypology]' => 1,
            'person_new_group[peopleGroup][nbPeople]' => 1,
            'person_new_group[role]' => 1,
        ]);

        $this->assertSelectorTextContains('.alert.alert-success', 'Le nouveau groupe est créé.');

        return $crawler;
    }
}
