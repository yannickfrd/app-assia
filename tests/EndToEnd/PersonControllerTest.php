<?php

namespace App\Tests\EndToEnd;

use App\Entity\Person;
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
            dirname(__DIR__).'/DataFixturesTest/PersonFixturesTest.yaml',
        ]);

        $this->person = $this->dataFixtures['person1'];
    }

    public function testPantherEditPersonInGroupWithAjax()
    {
        $this->createPantherLogin();

        dump('Test : go to people search page');

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generatePantherUri('people'));

        $form = $crawler->selectButton('search')->form([
            'firstname' => 'John',
            'lastname' => 'DOE',
        ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        $this->client->waitFor('table');

        $link = $crawler->selectLink('DOE')->link();

        dump('Test : go to person page');

        /** @var Crawler */
        $crawler = $this->client->click($link);

        $this->client->waitFor('#updatePerson');

        dump('Test : update information from the person');

        $form = $crawler->selectButton('updatePerson')->form([]);

        $this->client->submit($form);

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        // testPantherEditPersonWithAjax
        $crawler->selectButton('btn-close-msg')->click();

        dump('Test : create a new group for the person');

        // testPantherSuccessToAddNewGroupToPerson
        $this->client->waitFor('#btn-new-group');
        $crawler->selectButton('btn-new-group')->click();
        sleep(1); // $this->client->waitFor("#js-btn-confirm");

        $form = $crawler->selectButton('js-btn-confirm')->form([
            'person_new_group[groupPeople][familyTypology]' => 1,
            'person_new_group[groupPeople][nbPeople]' => 1,
            'person_new_group[role]' => 1,
        ]);

        $this->client->submit($form);

        $this->assertSelectorTextContains('.alert.alert-success', 'Le nouveau groupe a été créé.');
    }
}
