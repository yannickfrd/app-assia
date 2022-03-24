<?php

namespace App\Tests\Controller\Rdv;

use App\Entity\Support\SupportGroup;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CalendarControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $fixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        /** @var AbstractDatabaseTool */
        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/rdv_fixtures_test.yaml',
        ]);

        $this->supportGroup = $this->fixtures['support_group1'];
    }

    public function testShowCalendarIsUp(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/calendar/month');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Agenda');

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/calendar/month");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Agenda');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
