<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class PersonEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    protected Client $client;

    public function testPerson(): void
    {
        $this->client = $this->loginUser('john_user');

        $this->outputMsg('Show people search page');

        $this->client->request('GET', '/people');

        $this->assertSelectorTextContains('h1', 'Rechercher une personne');

        $this->searchPerson();
        $this->editPerson();
        $this->createNewGroupForPerson();
    }

    private function searchPerson(): void
    {
        $this->outputMsg('Search a person');

        $crawler = $this->client->waitFor('form#people_search');
        $crawler->selectButton('search')->form([
            'firstname' => 'John',
            'lastname' => 'DOE',
        ]);

        $this->clickElement('a.btn[title="Voir la fiche de la personne"]');
    }

    private function editPerson(): void
    {
        $this->outputMsg('Edit person');

        $this->fixScrollBehavior();

        $this->clickElement('#updatePerson');

        $this->client->waitFor('#alert-message');
        $this->assertSelectorExists('#alert-message.toast.alert-success');

        $this->clickElement('.toast .btn-close');
    }

    private function createNewGroupForPerson(): void
    {
        $this->outputMsg('Create a new group for the person');

        $this->clickElement('#btn-new-group');

        $crawler = $this->client->waitFor('form[name="person_new_group"]');
        sleep(1);
        $crawler->selectButton('js-btn-confirm')->form([
            'person_new_group[peopleGroup][familyTypology]' => 1,
            'person_new_group[peopleGroup][nbPeople]' => 1,
            'person_new_group[role]' => 1,
        ]);

        $this->clickElement('#js-btn-confirm');

        $this->assertSelectorTextContains('.toast.alert-success', 'Le nouveau groupe est créé.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client->quit();
    }
}
