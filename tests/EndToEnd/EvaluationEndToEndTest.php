<?php

namespace App\Tests\EndToEnd;

use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class EvaluationEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    /** @var PantherClient */
    protected $client;

    protected function setUp(): void
    {
        $this->client = $this->createPantherLogin();

        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testEvaluation()
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', '/support/1/view');

        $this->outputMsg('Go to the evaluation page');

        sleep(1);
        $link = $crawler->filter('a#support-evaluation')->link();

        $crawler = $this->client->click($link);

        $this->client->waitFor('#accordion-parent-init_eval');
        $this->assertSelectorTextContains('h1', 'Évaluation sociale');

        $this->outputMsg('Edit the evaluation');

        $crawler = $this->client->submitForm('send');

        $this->client->waitFor('div[data-edit-mode]');

        $this->client->waitFor('.alert.alert-success');
        $this->assertSelectorExists('.alert.alert-success');
        // $crawler->selectButton('btn-close-msg')->click();

        return $crawler;
    }
}
