<?php

namespace App\Tests\EndToEnd;

use App\Entity\People\Person;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class PersonControllerTest extends PantherTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var PantherClient */
    protected $client;

    /** @var array */
    protected $dataFixtures;

    /** @var Person */
    protected $person;

    protected function setUp()
    {
        $this->dataFixtures = $this->loadFixtureFiles([
            // dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            // dirname(__DIR__).'/DataFixturesTest/PersonFixturesTest.yaml',
        ]);

        // $this->person = $this->dataFixtures['person1'];
    }

    public function testPantherEditPersonInGroupWithAjax()
    {
        $this->createPantherLogin();

        $this->debug('go to people search page');

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generatePantherUri('people'));

        $form = $crawler->selectButton('search')->form([
            'firstname' => 'John',
            'lastname' => 'DOE',
        ]);

        $this->client->waitFor('a.btn[title="Voir la fiche de la personne"]');
        // sleep(1);

        $link = $crawler->filter('a.btn[title="Voir la fiche de la personne"]')->link();

        $this->debug('go to person page');

        /** @var Crawler */
        $crawler = $this->client->click($link);

        // $this->client->waitFor('#updatePerson');
        $this->debug('update information from the person');
        // sleep(1);
        $crawler->selectButton('updatePerson')->click();

        $this->debug('close message-flash');

        $this->client->waitFor('#js-msg-flash');
        // sleep(1);
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        // testPantherEditPersonWithAjax
        $crawler->selectButton('btn-close-msg')->click();

        $this->debug('create a new group for the person');

        // testPantherSuccessToAddNewGroupToPerson
        $this->debug('select btn-new-group');
        $crawler->selectButton('btn-new-group')->click();
        sleep(1); // $this->client->waitFor("#js-btn-confirm");

        $this->debug('sign form');

        $form = $crawler->selectButton('js-btn-confirm')->form([
            'person_new_group[peopleGroup][familyTypology]' => 1,
            'person_new_group[peopleGroup][nbPeople]' => 1,
            'person_new_group[role]' => 1,
        ]);

        $this->client->submit($form);

        $this->assertSelectorTextContains('.alert.alert-success', 'Le nouveau groupe est créé.');
    }
}
