<?php

declare(strict_types=1);

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class PaymentEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    protected Client $client;

    protected function setUp(): void
    {
        $this->faker = \Faker\Factory::create('fr_FR');
    }

//    public function testPayment(): void
//    {
//        $this->client = $this->loginUser();
//
//        /** @var Crawler */
//        $crawler = $this->client->request('GET', '/support/1/show');
//        $this->assertSelectorTextContains('h1', 'Suivi');
//
//        sleep(5);
//        $this->client->getWebDriver()->findElement(WebDriverBy::name('#support-payments'))->click();
//
//        $this->restorePayment();
//    }

    protected function restorePayment()
    {
        $this->outputMsg('Restore a payment');

        $this->cssClick('label[for="deleted_deleted"]');
        $this->cssClick('button[id="search"]');

        $this->client->waitFor('table', 1);
        $this->client->getWebDriver()->findElement(WebDriverBy::name('restore'))->click();

        $this->client->waitFor('#js-msg-flash', 3);
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');
    }
}