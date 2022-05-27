<?php

namespace App\Tests\EndToEnd\Traits;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Panther\Client;

trait AppPantherTestTrait
{
    protected ConsoleOutput $output;
    protected Client $client;

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

    protected function acceptWindowConfirm(): void
    {
        $this->client->wait()->until(WebDriverExpectedCondition::alertIsPresent());
        $this->client->getWebDriver()->switchTo()->alert()->accept();
    }

    protected function clickElement(string $selector, int $timeoutInSecond = 10): void
    {
        $this->client->waitFor($selector, $timeoutInSecond);

        $this->client
            ->getWebDriver()
            ->findElement(WebDriverBy::cssSelector($selector))
            ->click();
    }

    protected function setForm(string $selector, ?array $values = null): void
    {
        $this->client->waitFor($selector);

        $this->client
            ->getCrawler()
            ->filter($selector)
            ->form($values)
        ;
    }

    /**
     * Fix the bug with scroll-behavior ('element click intercepted: Element is not clickable at point').
     */
    protected function fixScrollBehavior(): void
    {
        $this->client->executeScript('document.querySelector("html").style.scrollBehavior = "auto";');
    }
}
