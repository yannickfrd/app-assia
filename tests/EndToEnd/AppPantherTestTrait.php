<?php

namespace App\Tests\EndToEnd;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;

trait AppPantherTestTrait
{
    /** @var ConsoleOutput */
    protected $output;

    /** @var PantherClient */
    protected $client;

    protected function createPantherLogin(): Client
    {
        $this->output = new ConsoleOutput();
        $this->documentsDirectory = dirname(__DIR__).'/../../public/uploads/documents/';

        $this->client = static::createClient();

        $this->client = static::createPantherClient(['browser' => 'firefox']);
        // $this->client = Client::createChromeClient(__DIR__.'/../drivers/chromedriver');
        // $this->client = Client::createFirefoxClient(__DIR__.'/../drivers/geckodriver');

        /** @var Crawler */
        $crawler = $this->client->request('GET', '/deconnexion');
        $crawler = $this->client->request('GET', '/login');

        $this->outputMsg('Try to login');

        $form = $crawler->selectButton('send')->form([
            '_username' => 'r.user',
            '_password' => 'Test123*',
        ]);

        $this->client->submit($form);

        $this->assertSelectorTextContains('h1', 'Tableau de bord');

        return $this->client;
    }

    protected function outputMsg(string $message, bool $newline = false)
    {
        $this->output->write("\e[34mtest : \e[36m".$message."\e[0m \n", $newline);
    }
}
