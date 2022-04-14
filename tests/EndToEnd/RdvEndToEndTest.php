<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class RdvEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    protected Client $client;

    protected function setUp(): void
    {
        $this->client = $this->loginUser();

        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testRdv(): void
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', '/support/1/show');

        $crawler = $this->goToCalendar($crawler);
        $crawler = $this->createRdv($crawler);
        $crawler = $this->editRdv($crawler);

//         $crawler = $this->deleteRdv($crawler);

         $this->restoreRdv();

         $this->client->quit();
    }

    private function goToCalendar(Crawler $crawler): Crawler
    {
        $this->outputMsg('Show calendar page');

        $link = $crawler->filter('#support-calendar')->link();

        /** @var Crawler */
        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Rendez-vous');
        // $this->client->waitForVisibility('#js-new-rdv');
        sleep(1);

        return $crawler;
    }

    private function createRdv(Crawler $crawler): Crawler
    {
        $this->outputMsg('Create a rdv');

        $crawler->selectButton('js-new-rdv')->click();

        $this->client->waitForVisibility('#modal-rdv', 1);
        /** @var Crawler */
        $crawler = $this->client->submitForm('save-rdv', [
            'rdv[title]' => $this->faker->sentence(mt_rand(5, 10), true),
            'start' => '10:30',
            'end' => '12:30',
            'rdv[content]' => join('. ', $this->faker->paragraphs(mt_rand(1, 2))),
        ]);
        sleep(1);

        $this->client->waitFor('#js-msg-flash', 1);
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); // pop-up effect

        return $crawler;
    }

    private function editRdv(Crawler $crawler): Crawler
    {
        $this->outputMsg('Edit a rdv');

        $crawler->filter('#show-weekend')->click();

        $crawler->filter('a.calendar-event')->first()->click();
        sleep(1); // pop-up effect

        /** @var Crawler */
        $crawler = $this->client->submitForm('save-rdv', [
            'rdv[title]' => $this->faker->sentence(mt_rand(5, 10), true),
            'rdv[content]' => join('. ', $this->faker->paragraphs(mt_rand(1, 2))),
            'rdv[users]' => [1],
        ]);
        sleep(1);

        $this->client->waitFor('#js-msg-flash', 1);
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); // pop-up effect

        return $crawler;
    }

    private function deleteRdv(Crawler $crawler): Crawler
    {
        $this->outputMsg('Delete a rdv');

        $crawler->filter('a.calendar-event')->first()->click();
        sleep(1); // pop-up effect

        $crawler->filter('modal-btn-delete')->click();

        sleep(1);
        $this->assertSelectorExists('#js-msg-flash.alert.alert-warining');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(5); // pop-up effect

        return $crawler;
    }

    private function restoreRdv(): void
    {
        $this->outputMsg('Restore a rdv');

        $this->cssClick('a[data-original-title="Passer en vue liste"]');

        $this->cssClick('button[data-action="delete-rdv"]');

        $this->client->waitForVisibility('#modal-block', 1);
        $this->cssClick('#modal-block button#modal-confirm');

        $this->client->waitFor('.alert', 3);
        $this->assertSelectorExists('.alert.alert-warning');

        $this->cssClick('button[aria-label="Close"]');

        $this->cssClick('label[for="deleted_deleted"]');
        $this->cssClick('button[id="search"]');

        $this->client->waitFor('table', 1);
        $this->client->getWebDriver()->findElement(WebDriverBy::name('restore'))->click();

        $this->client->waitFor('.alert', 3);
        $this->assertSelectorExists('.alert.alert-success');
    }
}
