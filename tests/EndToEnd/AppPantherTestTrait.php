<?php

namespace App\Tests\EndToEnd;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Panther\Client;

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

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();

        $this->client->request('GET', '/logout');
        $this->client->request('GET', '/login');

        $this->outputMsg('Try to login');

        $this->client->submitForm('send', [
            'username' => 'r.user',
            'password' => 'Test123*',
        ]);

        $this->assertSelectorTextContains('h1', 'Tableau de bord');

        return $this->client;
    }

    protected function outputMsg(string $message, bool $newline = false)
    {
        $this->output->write("\e[34mtest : \e[36m".$message."\e[0m \n", $newline);
    }
}
