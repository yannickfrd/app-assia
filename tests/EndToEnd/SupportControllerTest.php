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

        /** @var Crawler */
        $crawler = $this->client->request('GET', $this->generatePantherUri('support_edit', [
            'id' => 1,
        ]));

        //
        //
        //
        //
        // Test de la page des notes sociales
        $link = $crawler->filter('a[title="Notes sociales et rapport"]')->link();

        /** @var Crawler */
        $crawler = $this->client->click($link);

        $this->client->waitFor('#container-notes');
        $this->assertSelectorTextContains('h1', 'Notes sociales');

        $form = $crawler->selectButton('js-new-note')->click();
        sleep(1); //pop-up effect

        // Tentative d'écrire dans la div #editor
        $this->client->executeScript('document.getElementById("editor").innerHTML="<p>contenu de la note</p>"');
        $this->client->executeScript('document.getElementById("note_content").textContent="contenu de la note."');

        $form = $crawler->selectButton('js-btn-save')->form([
            'note[title]' => $faker->sentence(mt_rand(5, 10), true),
        ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-danger');

        $crawler->selectButton('btn-close-msg')->click();

        $crawler->selectButton('js-btn-cancel')->click();
        sleep(1);

        $link = $crawler->filter('#container-notes div.js-note')->eq(0)->click();
        sleep(1); //pop-up effect

        $form = $crawler->selectButton('js-btn-save')->form([
            'note[title]' => $faker->sentence(mt_rand(5, 10), true),
        ]);

        /** @var Crawler */
        $crawler = $this->client->submit($form);

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        //
        //
        //
        //
        // Test de la page des rendez-vous
        $link = $crawler->filter('a[title="Documents administratifs"]')->link();

        /** @var Crawler */
        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Documents');
        sleep(1); // $this->client->waitFor('#js-new-rdv');

        $crawler->selectButton('js-new-document')->click();
        sleep(1); //pop-up effect

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

        $crawler->filter('td.js-document-name')->eq(0)->click();
        sleep(1); //pop-up effect

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
        sleep(1);

        //
        //
        //
        //
        //
        // Test de la page des rendez-vous
        $link = $crawler->filter('a[title="Rendez-vous"]')->link();

        /** @var Crawler */
        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Rendez-vous');
        // $this->client->waitFor('#js-new-rdv');
        sleep(1);

        $crawler->selectButton('js-new-rdv')->click();
        sleep(1); //pop-up effect

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

        $link = $crawler->filter('a.calendar-event')->eq(0)->click();
        sleep(1); //pop-up effect

        $form = $crawler->selectButton('js-btn-save')->form([
            'rdv[title]' => $faker->sentence(mt_rand(5, 10), true),
            'rdv[content]' => join('. ', $faker->paragraphs(mt_rand(1, 2))),
        ]);

        $crawler = $this->client->submit($form);

        sleep(1);
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        //
        //
        //
        //
        //
        // Test de la page du suivi social
        $crawler = $this->client->request('GET', $this->generatePantherUri('supports'));

        $form = $crawler->selectButton('search')->form([
            'fullname' => 'Doe',
        ]);

        $crawler = $this->client->submit($form);
        // $this->client->waitFor('table');
        sleep(1);

        $link = $crawler->filter('table tbody tr a.btn')->eq(0)->link();

        $crawler = $this->client->click($link);
        sleep(1);

        $this->assertSelectorTextContains('h1', 'Suivi social');

        $form = $crawler->selectButton('send')->form([]);

        $crawler = $this->client->submit($form);

        $this->assertSelectorExists('.alert.alert-success');

        //
        //
        //
        //
        // Test de la page d'évaluation sociale
        // $link = $crawler->selectLink('support-evaluation')->click();
        $link = $crawler->filter('a[title="Évaluation sociale"]')->link();

        $crawler = $this->client->click($link);

        $this->client->waitFor('#accordion-parent-init_eval');
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');

        $form = $crawler->selectButton('send')->form([]);

        $crawler = $this->client->submit($form);

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');
    }
}
