<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class PersonEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    public const BUTTON_NEW = '#updatePerson';

    public const ALERT_SUCCESS = '.toast.show.alert-success';
    public const ALERT_WARNING = '.toast.show.alert-warning';
    public const BUTTON_CLOSE_MSG = '.toast.show .btn-close';

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

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
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

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorTextContains(self::ALERT_SUCCESS, 'Le nouveau groupe a été créé.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client->quit();
    }
}
