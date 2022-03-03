<?php

namespace App\Tests\Controller\Event;

use App\Entity\Event\Task;
use App\Entity\Support\SupportGroup;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class TaskControllerTest extends WebTestCase
{
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Task */
    protected $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/task_fixtures_test.yaml',
        ]);

        $this->createLogin($fixtures['userRoleUser']);

        $this->user = $fixtures['userRoleUser'];
        $this->supportGroup = $fixtures['supportGroup1'];
        $this->task = $fixtures['task1'];
    }

    public function testSearchTasksIsSuccessful(): void
    {
        $this->client->request('GET', '/tasks');

        // Page is up
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tâches');

        // Search is successful
        $this->client->submitForm('search', [
            'date[end]' => (new \DateTime())->format('Y-m-d'),
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tâches');
    }

    public function testExportTasksIsSuccessful(): void
    {
        $this->client->request('GET', '/tasks');

        // Export with no result
        $this->client->submitForm('export', [
            'date[start]' => (new \Datetime())->modify('+10 year')->format('Y-m-d'),
        ], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-warning', 'Aucun résultat à exporter');

        // Export with results
        $this->client->submitForm('export', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testSupportTasksIndexIsUp(): void
    {
        $this->client->request('GET', "/support/{$this->supportGroup->getId()}/tasks");
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tâches');
    }

    public function testExportSupportTasksIsSuccessful(): void
    {
        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/tasks");

        $this->client->submitForm('export', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreateNewTaskIsSuccessful(): void
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', '/tasks');
        $csrfToken = $crawler->filter('#task__token')->attr('value');
        $now = new \DateTime();

        // Fail
        $this->client->request('POST', '/task/new');

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('danger', $content['alert']);

        // Success
        $this->client->request('POST', '/task/new', [
            'task' => [
                'title' => 'Task test',
                'level' => Task::MEDIUM_LEVEL,
                'users' => [$this->user->getId()],
                'supportGroup' => $this->supportGroup->getId(),
                'end' => (clone $now)->modify('+1 hour')->format('Y-m-d\TH:00'),
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('create', $content['action']);
        $this->assertSame('Task test', $content['task']['title']);
    }

    public function testCreateNewTaskWithSupportIsSuccessful(): void
    {
        $id = $this->supportGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/tasks");

        $this->assertResponseIsSuccessful();

        $csrfToken = $crawler->filter('#task__token')->attr('value');

        // // Fail
        $this->client->request('POST', "/support/$id/task/new");

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);

        // Success
        $this->client->request('POST', "/support/$id/task/new", [
            'task' => [
                'title' => 'Task test',
                'level' => Task::MEDIUM_LEVEL,
                'users' => [$this->user->getId()],
                'supportGroup' => $this->supportGroup->getId(),
                'end' => (clone new \DateTime())->modify('+1 hour')->format('Y-m-d\TH:00'),
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('create', $content['action']);
        $this->assertSame('Task test', $content['task']['title']);
    }

    public function testShowTaskIsSuccessful(): void
    {
        $id = $this->task->getId();
        $this->client->request('GET', "/task/$id/show");
        $this->assertResponseIsSuccessful();

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('show', $content['action']);
    }

    public function testEditTaskIsSuccessful(): void
    {
        /** @var Crawler */
        $crawler = $this->client->request('GET', '/tasks');
        $id = $this->task->getId();

        // Fail
        $this->client->request('POST', "/task/$id/edit");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);

        // Success
        $this->client->request('POST', "/task/$id/edit", [
            'task' => [
                'title' => 'Task edit',
                'location' => $this->task->getLocation(),
                'content' => $this->task->getContent(),
                'level' => Task::MEDIUM_LEVEL,
                'users' => [$this->user->getId()],
                'supportGroup' => $this->supportGroup->getId(),
                'end' => (clone new \DateTime())->modify('+1 hour')->format('Y-m-d\TH:00'),
                '_token' => $crawler->filter('#task__token')->attr('value'),
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('edit', $content['action']);
        $this->assertSame('Task edit', $content['task']['title']);
    }

    public function testDeleteTaskIsSuccessful(): void
    {
        $id = $this->task->getId();
        $this->client->request('GET', "/task/$id/delete");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('delete', $content['action']);
    }

    public function testToggleStatusIsSuccessful(): void
    {
        $id = $this->task->getId();
        $this->client->request('GET', "/task/$id/toggle-status");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('toggle_status', $content['action']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->user = null;
        $this->supportGroup = null;
        $this->task = null;
    }
}
