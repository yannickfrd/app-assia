<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class EvaluationEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    protected Client $client;

    public function testEvaluation(): void
    {
        $this->client = $this->loginUser();

        /** @var Crawler */
        $crawler = $this->client->request('GET', '/support/1/show');

        $crawler = $this->showEvaluation($crawler);
        $crawler = $this->editEvaluation($crawler);
    }

    private function showEvaluation(Crawler $crawler): Crawler
    {
        $this->outputMsg('Show the evaluation page');

        sleep(1);
        $link = $crawler->filter('a#support-evaluation')->link();

        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Évaluation sociale');

        return $crawler;
    }

    private function editEvaluation(Crawler $crawler): Crawler
    {
        $this->outputMsg('Edit the evaluation');

        $this->client->waitForVisibility('#card-evalHousing');
        $crawler->filter('#card-evalHousing')->click();

        $crawler->filter('#card-evalBackground')->click();

        $this->client->waitForVisibility('#card-evalBackground button[type="submit"]');
        $crawler->filter('#card-evalBackground button[type="submit"]')->click();

        $this->client->waitForVisibility('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();

        return $crawler;
    }
}
