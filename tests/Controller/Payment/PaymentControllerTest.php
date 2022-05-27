<?php

namespace App\Tests\Controller\Payment;

use App\Entity\Support\Payment;
use App\Entity\Support\SupportGroup;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DomCrawler\Crawler;

class PaymentControllerTest extends WebTestCase
{
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

        $this->client = static::createClient();
        $this->client->followRedirects();

        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/payment_fixtures_test.yaml',
        ]);

        $this->supportGroup = $this->fixtures['support_group1'];
        $this->payment = $this->fixtures['payment1'];
    }

    public function testSearchPaymentsIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/payments');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Paiements');

        /** @var Crawler */
        $crawler = $this->client->submitForm('search', [
            'date[start]' => '2020-04-01',
            'date[end]' => '2020-04-30',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThanOrEqual(4, $crawler->filter('td[scope="row"]')->count());
    }

    public function testExportPaymentsIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/payments');

        // Empty export 1
        $this->client->submitForm('export', [
            'date[start]' => (new \DateTime())->modify('+1 year')->format('Y-m-d'),
        ], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-warning', 'Aucun résultat à exporter.');

        // Export 1
        $this->client->submitForm('export', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));

        $this->client->request('GET', '/payments');

        // Empty export 2
        $this->client->submitForm('export-accounting', [
            'date[start]' => (new \DateTime())->modify('+1 year')->format('Y-m-d'),
        ], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-warning', 'Aucun résultat à exporter.');

        // Export 2
        $this->client->submitForm('export-accounting', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testExportDeltaPaymentsIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $this->client->request('GET', '/payments');

        $this->client->submitForm('export-delta', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testShowPaymentIndicatorsIsUp(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/payment/indicators');

        $this->assertResponseIsSuccessful();

        $this->client->submitForm('search');

        $this->assertResponseIsSuccessful();
    }

    public function testShowSupportListPaymentsIsUp(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', "/support/{$this->supportGroup->getId()}/payments");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Paiements');
    }

    public function testExportSupportPaymentsIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', "/support/{$this->supportGroup->getId()}/payments");

        $this->client->submitForm('export', [], 'GET');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('spreadsheetml.sheet', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testCreatePaymentIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->supportGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$id/payments");
        $csrfToken = $crawler->filter('#payment__token')->attr('value');

        // Fail
        $this->client->request('POST', "/support/$id/payment/new", [
            'payment[type]' => Payment::CONTRIBUTION,
        ]);

        $this->assertResponseIsSuccessful();
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

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('create', $content['action']);
    }

    public function testEditPaymentIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $id = $this->payment->getId();
        $supportId = $this->supportGroup->getId();
        /** @var Crawler */
        $crawler = $this->client->request('GET', "/support/$supportId/payments");
        $csrfToken = $crawler->filter('#payment__token')->attr('value');

        // Fail
        $this->client->request('POST', "/payment/$id/edit", []);

        $this->assertResponseIsSuccessful();
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

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('update', $content['action']);
    }

    public function testUpdatePaymentWithOtherUserIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user4']);

        $crawler = $this->client->request('GET', '/support/1/payments');
        $this->assertResponseIsSuccessful();

        $this->client->request('POST', "/payment/{$this->payment->getId()}/edit", [
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

    public function testGetPaymentIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', "/payment/{$this->payment->getId()}/get");

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('show', $content['action']);
    }

    public function testDeletePaymentIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', "/payment/{$this->payment->getId()}/delete");

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('delete', $content['action']);
    }

    public function testRestorePaymentIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $paymentId = $this->payment->getId();
        $this->client->request('GET', "/payment/$paymentId/delete");

        // After delete a payment
        $id = $this->supportGroup->getId();
        $crawler = $this->client->request('GET', "/support/$id/payments", [
            'deleted' => ['deleted' => true],
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('tbody tr')->count());

        $this->client->request('GET', "/payment/$paymentId/restore");
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('restore', $content['action']);

        // After restore a payment
        $crawler = $this->client->request('GET', "/support/$id/payments", [
            'deleted' => ['deleted' => true],
        ]);
        $this->assertSame(0, $crawler->filter('tbody tr')->count());
    }

    public function testExportPaymentToPdfIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', "/payment/{$this->payment->getId()}/export/pdf");

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('application/pdf', $this->client->getResponse()->headers->get('content-type'));
    }

    public function testSendPaymentByEmailIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        // Fail
        $id = $this->fixtures['payment2']->getId();
        $this->client->request('GET', "/payment/$id/send/pdf");

        $this->assertResponseIsSuccessful();
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Le suivi n\'a pas d\'adresse e-mail renseignée.', $content['msg']);

        // Success
        $id = $this->payment->getId();
        $this->client->request('GET', "/payment/$id/send/pdf");

        $this->assertResponseIsSuccessful();

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Le reçu du paiement a été envoyé par email.', $content['msg']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $cache->clear();
    }
}
