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
    }

    public function testSupport()
    {
        $faker = \Faker\Factory::create('fr_FR');

        $this->createPantherLogin();

        // Test de la page du suivi social

        dump('Test : go to supports page');

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generatePantherUri('supports'));

        dump('Test : search a support');

        $form = $crawler->selectButton('search')->form([
            'fullname' => 'Doe',
        ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);
        // $this->client->waitFor('table');
        sleep(1);

        dump('Test : select a support');

        $link = $crawler->filter('table tbody tr a.btn')->eq(0)->link();

        dump('Test : go to a support page');

        $crawler = $this->client->click($link);
        sleep(1);

        $this->assertSelectorTextContains('h1', 'Suivi social');

        dump('Test : success to edit a support');

        $form = $crawler->selectButton('send')->form([]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        $this->assertSelectorExists('.alert.alert-success');
        // sleep(1);

        //
        //
        //
        //
        //
        // Test de la page d'évaluation sociale
        // $link = $crawler->selectLink('support-evaluation')->click();

        dump('Test : go to the evaluation page');

        $link = $crawler->filter('a#scroll-top')->click();
        sleep(1);

        $link = $crawler->filter('a[title="Évaluation sociale"]')->link();

        $crawler = $this->client->click($link);

        $this->client->waitFor('#accordion-parent-init_eval');
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');

        dump('Test : success to edit the evaluation');

        $form = $crawler->selectButton('send')->form([]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);
        sleep(1);

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        // sleep(1);

        //
        //
        //
        //
        // Test de la page des notes sociales

        $link = $crawler->filter('a#scroll-top')->click();
        sleep(1);

        dump('Test : go to the note page');

        $link = $crawler->filter('a[title="Notes sociales"]')->link();

        /** @var Crawler */
        $crawler = $this->client->click($link);

        $this->client->waitFor('#container-notes');
        $this->assertSelectorTextContains('h1', 'Notes sociales');

        dump('Test : select a new note');

        $form = $crawler->selectButton('js-new-note')->click();
        sleep(1); //pop-up effect

        dump('Test : fail to create a new note');

        $form = $crawler->selectButton('js-btn-save')->form([
            'note[title]' => $faker->sentence(mt_rand(5, 10), true),
        ]);
        // $this->client->executeScript('document.getElementById("editor").innerHTML="<p>contenu de la note</p>"');

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-danger');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1);

        dump('Test : success to create a new note');

        $form = $crawler->selectButton('js-btn-save')->form([
            'note[title]' => $faker->sentence(mt_rand(5, 10), true),
            'note[editor]' => join('. ', $faker->paragraphs(mt_rand(1, 2))),
        ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(2);

        dump('Test : get an old note');

        // $this->client->waitFor('#container-notes div.js-note');
        $link = $crawler->filter('#container-notes div.js-note')->eq(1)->click();
        sleep(1); //pop-up effect

        dump('Test : edit an old note');

        $this->client->waitFor('#js-btn-save');
        $form = $crawler->selectButton('js-btn-save')->form([
            'note[title]' => $faker->sentence(mt_rand(5, 10), true),
        ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');
        $crawler->selectButton('btn-close-msg')->click();
        // sleep(1);

        //
        //
        //
        //
        // Test de la page des rendez-vous

        dump('Test : go to documents page');

        $link = $crawler->filter('a#scroll-top')->click();
        sleep(1);

        $link = $crawler->filter('a[title="Documents administratifs"]')->link();

        /** @var Crawler */
        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Documents');
        sleep(1); // $this->client->waitFor('#js-new-rdv');

        dump('Test : select a new document');

        $crawler->selectButton('js-new-document')->click();
        sleep(1); //pop-up effect

        dump('Test : success to create a new document');

        $form = $crawler->selectButton('js-btn-save')->form([
            'document[type]' => mt_rand(1, 9),
            ]);

        /** @var FormField $fileFormField */
        $fileFormField = $form['document[file]'];
        $fileFormField->setValue(__DIR__.'/image_test.png');

        /** @var Crawler */
        $crawler = $this->client->submit($form);
        sleep(1); //pop-up effect

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        dump('Test : get a document');

        $this->client->waitFor('td.js-document-name');

        $crawler->filter('td.js-document-name')->eq(0)->click();
        sleep(1); //pop-up effect

        dump('Test : edit an old document');

        $form = $crawler->selectButton('js-btn-save')->form([
            'document[name]' => $faker->sentence(mt_rand(5, 10), true),
            'document[type]' => mt_rand(1, 9),
            'document[content]' => join('. ', $faker->paragraphs(mt_rand(1, 2))),
            ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        sleep(1);
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        // sleep(1);

        //
        //
        //
        //
        //
        // Test de la page des rendez-vous

        dump('Test : go to calendar page');

        $link = $crawler->filter('a#scroll-top')->click();
        sleep(1);

        $link = $crawler->filter('a[title="Rendez-vous"]')->link();

        /** @var Crawler */
        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Rendez-vous');
        // $this->client->waitFor('#js-new-rdv');
        sleep(1);

        dump('Test : select a new rdv');

        $crawler->selectButton('js-new-rdv')->click();
        sleep(1); //pop-up effect

        dump('Test : success to create a rdv');

        $form = $crawler->selectButton('js-btn-save')->form([
            'rdv[title]' => $faker->sentence(mt_rand(5, 10), true),
            'start' => '10:30',
            'end' => '12:30',
            'rdv[content]' => join('. ', $faker->paragraphs(mt_rand(1, 2))),
            ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);
        sleep(1);

        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        dump('Test : get an old rdv');

        $link = $crawler->filter('a.calendar-event')->eq(0)->click();
        sleep(1); //pop-up effect

        dump('Test : success to edit an old rdv');

        $form = $crawler->selectButton('js-btn-save')->form([
            'rdv[title]' => $faker->sentence(mt_rand(5, 10), true),
            'rdv[content]' => join('. ', $faker->paragraphs(mt_rand(1, 2))),
        ]);

        $crawler = $this->client->submit($form);

        sleep(1);
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');
    }
}
