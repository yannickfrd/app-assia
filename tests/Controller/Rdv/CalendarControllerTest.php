<?php

namespace App\Tests\Controller\Rdv;

use App\Entity\Support\Rdv;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CalendarControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    protected $data;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Rdv */
    protected $rdv;

    protected function setUp()
    {
        $this->data = $this->loadFixtureFiles([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/RdvFixturesTest.yaml',
        ]);

        $this->createLogin($this->data['userRoleUser']);

        $this->supportGroup = $this->data['supportGroup1'];
        $this->rdv = $this->data['rdv1'];
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

    public function testShowDayIsUp()
    {
        $now = new \DateTime();
        $year = $now->format('Y');
        $month = $now->format('m');
        $day = $now->format('d');
        $this->client->request('GET', "/calendar/day/$year/$month/$day");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h3', 'Jour');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->data = null;
    }
}
