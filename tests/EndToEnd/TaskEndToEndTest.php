<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class TaskEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    protected Client $client;

    public function testTask(): void
    {
        $this->client = $this->loginUser();

        /** @var Crawler */
        $crawler = $this->client->request('GET', '/support/1/show');

        $crawler = $this->goToIndex($crawler);
        $crawler = $this->createTask($crawler);
        $crawler = $this->editTask($crawler);
        $crawler = $this->toggleStatusTask($crawler);
        $crawler = $this->deleteTask($crawler);
        $this->restoreTask();
    }

    private function goToIndex(Crawler $crawler): Crawler
    {
        $this->outputMsg('Show tasks index page');

        $link = $crawler->filter('#support-tasks')->link();

        /** @var Crawler */
        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Tâches');
        sleep(1);

        return $crawler;
    }

    private function createTask(Crawler $crawler): Crawler
    {
        $this->outputMsg('Create a task');

        $this->client->waitForVisibility('#js_new_task');
        $crawler->selectButton('js_new_task')->click();
        sleep(1); //pop-up effect

        $this->client->waitForVisibility('#js-btn-save');
        $crawler->selectButton('js-btn-save')->form([
            'task[title]' => 'Task test',
            'task[_endDate]' => (new \DateTime())->modify('+1 week')->format('d/m/Y'),
            'task[_endTime]' => '16:00',
            'task[level]' => 3,
            'task[content]' => 'Content test',
            'task[tags]' => [1],
        ]);

        $crawler->filter('button[data-add-widget="alert"]')->first()->click();
        $crawler->filter('button[data-add-widget="alert"]')->first()->click();

        /** @var Crawler */
        $crawler = $this->client->submitForm('js-btn-save');
        sleep(1);

        $this->client->waitForVisibility('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        return $crawler;
    }

    private function editTask(Crawler $crawler): Crawler
    {
        $this->outputMsg('Edit a task');

        $crawler->filter('button[data-action="edit_task"]')->first()->click();
        sleep(1); //pop-up effect

        /** @var Crawler */
        $crawler = $this->client->submitForm('js-btn-save', [
            'task[title]' => 'Task title edit',
            'task[content]' => 'Content edit',
            'task[tags]' => [1, 2],
        ]);

        $this->client->waitForVisibility('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        return $crawler;
    }

    private function toggleStatusTask(Crawler $crawler): Crawler
    {
        $this->outputMsg('Toggle status task');

        $crawler->filter('input[data-action="toggle_task_status"]')->first()->click();
        sleep(1); //pop-up effect

        $this->client->waitForVisibility('#js-msg-flash');
        $this->assertSelectorExists('#js-msg-flash.alert.alert-success');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        return $crawler;
    }

    private function deleteTask(Crawler $crawler): Crawler
    {
        $this->outputMsg('Delete a task');

        $crawler->filter('button[data-action="edit_task"]')->first()->click();
        sleep(1); //pop-up effect

        $this->client->waitForVisibility('#modal-btn-delete');
        $crawler->selectButton('modal-btn-delete')->click();
        sleep(1);

        $this->client->waitForVisibility('#modal-block #modal-confirm');
        $crawler->selectButton('modal-confirm')->click();

        sleep(1);
        $this->assertSelectorExists('#js-msg-flash.alert.alert-warning');

        $crawler->selectButton('btn-close-msg')->click();
        sleep(1); //pop-up effect

        return $crawler;
    }

    private function restoreTask()
    {
        $this->outputMsg('Restore a task');

        $this->clickElement('button[type="reset"]');
        $this->clickElement('label[for="deleted_deleted"]');
        $this->clickElement('button[id="search"]');

        $this->client->waitFor('table', 1);
        $this->client->getWebDriver()->findElement(WebDriverBy::name('restore'))->click();

        $this->client->waitFor('.alert', 3);
        $this->assertSelectorExists('.alert.alert-success');
    }
}
