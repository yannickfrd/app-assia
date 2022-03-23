<?php

namespace App\Tests\EndToEnd\Traits;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Panther\Client;

trait AppPantherTestTrait
{
    /** @var ConsoleOutput */
    protected $output;

    /** @var PantherClient */
    protected $client;

    protected function loginUser(string $username = 'john_user'): Client
    {
        $this->output = new ConsoleOutput();

        $this->client = static::createPantherClient(['browser' => 'chrome']);

        (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->clear();

        $this->client->request('GET', '/login');

        $this->outputMsg('Try to login');

        $this->client->submitForm('send', [
            'username' => $username,
            'password' => 'password',
        ]);

        return $this->client;
    }

    protected function outputMsg(string $message, bool $newline = false): void
    {
        $this->output->write("\e[34mtest : \e[36m".$message."\e[0m \n", $newline);
    }
}
