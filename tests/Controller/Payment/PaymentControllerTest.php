<?php

namespace App\Tests\Controller\Payment;

use App\Entity\Support\Payment;
use App\Entity\Support\SupportGroup;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class PaymentControllerTest extends WebTestCase
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

    /** @var Payment */
    protected $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();

        /* @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PaymentFixturesTest.yaml',
        ]);

        $this->supportGroup = $this->fixtures['supportGroup1'];
        $this->payment = $this->fixtures['payment1'];
    }

    public function testSearchPaymentsIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/payments');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Paiements');

        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [
            'date[start]' => '2020-04-01',
            'date[end]' => '2020-04-30',
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertGreaterThanOrEqual(4, $crawler->filter('td[scope="row"]')->count());
    }

    public function testExportPaymentsIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/payments');

        // Empty export 1
        $this->client->submitForm('export', [
            'date[start]' => (new \Datetime())->modify('+1 year')->format('Y-m-d'),
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'Aucun résultat à exporter.');

        // Export 1
        $this->client->submitForm('export', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));

        $this->client->request('GET', '/payments');

        // Empty export 2
        $this->client->submitForm('export-accounting', [
            'date[start]' => (new \Datetime())->modify('+1 year')->format('Y-m-d'),
        ], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('.alert.alert-warning', 'Aucun résultat à exporter.');

        // Export 2
        $this->client->submitForm('export-accounting', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testExportDeltaPaymentsIsSuccessful()
    {
        $this->createLogin($this->fixtures['userSuperAdmin']);

        $this->client->request('GET', '/payments');

        $this->client->submitForm('export-delta', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testShowPaymentIndicatorsIsUp()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $this->client->request('GET', '/payment/indicators');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->submitForm('search');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testShowSupportListPaymentsIsUp()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/payments");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Paiements');
    }

    public function testExportSupportPaymentsIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->supportGroup->getId();
        $this->client->request('GET', "/support/$id/payments");

        $this->client->submitForm('export', [], 'GET');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreatePaymentIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->supportGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/payments");
        $csrfToken = $crawler->filter('#payment__token')->attr('value');

        // Fail
        $this->client->request('POST', "/support/$id/payment/new", [
            'payment[type]' => Payment::CONTRIBUTION,
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);

        // Success
        $this->client->request('POST', "/support/$id/payment/new", [
            'payment' => [
                'startDate' => '2021-01-01',
                'endDate' => '2021-01-31',
                'type' => Payment::CONTRIBUTION,
                'ressourcesAmt' => 1000,
                'toPayAmt' => 100,
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('create', $content['action']);
    }

    public function testUpdatePaymentIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->payment->getId();
        $supportId = $this->supportGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$supportId/payments");
        $csrfToken = $crawler->filter('#payment__token')->attr('value');

        // Fail
        $this->client->request('POST', "/payment/$id/edit", []);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('danger', $content['alert']);

        // Success
        $this->client->request('POST', "/payment/$id/edit", [
            'payment' => [
                'startDate' => '2021-01-01',
                'endDate' => '2021-01-31',
                'type' => Payment::CONTRIBUTION,
                'ressourcesAmt' => 1000,
                'toPayAmt' => 100,
                '_token' => $csrfToken,
            ],
        ]);

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('update', $content['action']);
    }

    public function testUpdatePaymentWithOtherUserIsSuccessful()
    {
        $this->createLogin($this->fixtures['user4']);

        $id = $this->payment->getId();
        $crawler = $this->client->request('GET', '/support/1/payments');
        $this->assertResponseIsSuccessful();

        $this->client->request('POST', "/payment/$id/edit", [
            'payment' => [
                'startDate' => '2021-01-01',
                'endDate' => '2021-01-31',
                'type' => Payment::CONTRIBUTION,
                'ressourcesAmt' => 1000,
                'toPayAmt' => 100,
                '_token' => $crawler->filter('#payment__token')->attr('value'),
            ],
        ]);


        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('update', $content['action']);
    }

    public function testGetPaymentIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->payment->getId();
        $this->client->request('GET', "/payment/$id/get");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('show', $content['action']);
    }

    public function testDeletePaymentIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->payment->getId();
        $this->client->request('GET', "/payment/$id/delete");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('delete', $content['action']);
    }

    public function testExportPaymentToPdfIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        $id = $this->payment->getId();
        $this->client->request('GET', "/payment/$id/export/pdf");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('application/pdf', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testSendPaymentByEmailIsSuccessful()
    {
        $this->createLogin($this->fixtures['userRoleUser']);

        // Fail
        $id = $this->fixtures['payment2']->getId();
        $this->client->request('GET', "/payment/$id/send/pdf");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Le suivi n\'a pas d\'adresse e-mail renseignée.', $content['msg']);

        // Success
        $id = $this->payment->getId();
        $this->client->request('GET', "/payment/$id/send/pdf");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Le reçu du paiement a été envoyé par email.', $content['msg']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $this->fixtures = null;

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();
    }
}
