<?php

namespace App\Tests\Controller\Rdv;

use App\Entity\Event\Rdv;
use App\Entity\Support\SupportGroup;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CalendarControllerTest extends WebTestCase
{
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Rdv */
    protected $rdv;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();

        /** @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/RdvFixturesTest.yaml',
        ]);

        $this->createLogin($this->fixtures['userRoleUser']);

        $this->supportGroup = $this->fixtures['supportGroup1'];
        $this->rdv = $this->fixtures['rdv1'];
    }

    public function testShowCalendarIsUp()
    {
        $this->client->request('GET', '/calendar/month');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h2', 'Agenda');

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/calendar/month");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h2', 'Agenda');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $this->fixtures = null;
    }
}
