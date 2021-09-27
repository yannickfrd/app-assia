<?php

namespace App\Tests\EndToEnd;

use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class RdvEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    /** @var PantherClient */
    protected $client;

    protected function setUp(): void
    {
        $this->client = $this->createPantherLogin();

        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testRdv()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', '/support/1/view');

        $crawler = $this->goToCalendar($crawler);
        $crawler = $this->createRdv($crawler);
        $crawler = $this->editRdv($crawler);
        // $crawler = $this->deleteRdv($crawler);
    }

    private function goToCalendar(Crawler $crawler)
    {
        $this->outputMsg('Go to calendar page');

        $link = $crawler->filter('#support-calendar')->link();

        /** @var Crawler */
        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Rendez-vous');
        // $this->client->waitFor('#js-new-rdv');
        sleep(1);

        return $crawler;
    }

    private function createRdv(Crawler $crawler)
    {
        $this->outputMsg('Create a rdv');

        $crawler->selectButton('js-new-rdv')->click();
        sleep(1); //pop-up effect

        /** @var Crawler */
        $crawler = $this->client->submitForm('js-btn-save', [
            'rdv[title]' => $this->faker->sentence(mt_rand(5, 10), true),
            'start' => '10:30',
            'end' => '12:30',
            'rdv[content]' => join('. ', $this->faker->paragraphs(mt_rand(1, 2))),
        ]);
        sleep(1);

        $this->client->waitFor('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        return $crawler;
    }

    private function editRdv(Crawler $crawler)
    {
        $this->outputMsg('Edit a rdv');

        $crawler->filter('a.calendar-event')->first()->click();
        sleep(1); //pop-up effect
            
        /** @var Crawler */
        $crawler = $this->client->submitForm('js-btn-save', [
            'rdv[title]' => $this->faker->sentence(mt_rand(5, 10), true),
            'rdv[content]' => join('. ', $this->faker->paragraphs(mt_rand(1, 2))),
        ]);

        sleep(1);
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        return $crawler;
    }

    private function deleteRdv(Crawler $crawler)
    {
        $this->outputMsg('Delete a rdv');

        $crawler->filter('a.calendar-event')->first()->click();
        sleep(1); //pop-up effect

        $crawler->filter('modal-btn-delete')->click();

        sleep(1);
        $this->assertSelectorExists('#js-msg-flash.alert.alert-warining');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        return $crawler;
    }
}
