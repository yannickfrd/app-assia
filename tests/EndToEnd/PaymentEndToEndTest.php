<?php

declare(strict_types=1);

namespace App\Tests\EndToEnd;

use App\Entity\Support\Payment;
use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class PaymentEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    public const CONTAINER = '#container_payments';

    public const BUTTON_NEW = 'button[data-action="new_payment"]';
    public const FIRST_BUTTON_SHOW = 'tr button[data-action="show"]';
    public const FIRST_BUTTON_DELETE = 'tr button[data-action="delete"]';
    public const FIRST_BUTTON_RESTORE = 'tr button[data-action="restore"]';
    
    public const MODAL = '#modal_payment';
    public const FORM = 'form[name="payment"]';
    public const MODAL_BUTTON_SAVE = self::MODAL.' button[data-action="save"]';
    public const MODAL_BUTTON_CLOSE = self::MODAL.' button[data-action="close"]';
    public const MODAL_BUTTON_DELETE = self::MODAL.' button[data-action="delete"]';
    public const BUTTON_CALCUL_CONTRIBUTION = self::MODAL.' #calcul_contribution_btn';

    public const MODAL_BUTTON_CONFIRM = '#modal_confirm_btn';

    public const ALERT_SUCCESS = '.toast.show.alert-success';
    public const ALERT_WARNING = '.toast.show.alert-warning';
    public const BUTTON_CLOSE_MSG = '.toast.show .btn-close';

    protected Client $client;

    /** @var \Faker\Generator */
    protected $faker;

    protected function setUp(): void
    {
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testPayment(): void
    {
        $this->client = $this->loginUser('john_user');

        $this->client->request('GET', '/support/1/show');

        $this->showPaymentsIndex();
        $this->createPayment();
        $this->editPayment();
        $this->deletePaymentByModal();
    }

    public function testRestorePayment(): void
    {
        $this->client = $this->loginUser('user_super_admin');

        $this->client->request('GET', '/support/1/payments');

        $this->restorePayment();
    }

    private function showPaymentsIndex(): void
    {
        $this->outputMsg('Show payments index page');

        $this->clickElement('#support-payments');

        $this->assertSelectorTextContains('h1', 'Paiements');

        $this->fixScrollBehavior();
    }

    private function createPayment(): void
    {
        $this->outputMsg('Create a payment');

        $this->clickElement(self::BUTTON_NEW);
        sleep(1); // transition delay

        $this->setForm(self::FORM, [
            'payment[type]' => Payment::CONTRIBUTION,
        ]);

        sleep(1); // transition delay

        $this->clickElement(self::BUTTON_CALCUL_CONTRIBUTION);
        sleep(2); // transition delay

        $this->clickElement('#contribution_calcul_modal button[data-action="close"]');
        sleep(2); // transition delay

        $this->clickElement(self::MODAL_BUTTON_SAVE);

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function editPayment(): void
    {
        $this->outputMsg('Edit a payment');

        $this->clickElement(self::FIRST_BUTTON_SHOW);
        sleep(2); // transition delay

        $year = (new \DateTime())->format('Y');

        $this->setForm(self::FORM, [
            'payment[startDate]' => "01/01/$year",
            'payment[endDate]' => "31/01/$year",
            'payment[resourcesAmt]' => 1500,
            'payment[toPayAmt]' => 150,
            'payment[paymentDate]' => "05/02/$year",
            'payment[paymentType]' => Payment::DEFAULT_TYPE,
            'payment[paidAmt]' => 150,
        ]);

        $this->clickElement(self::MODAL_BUTTON_SAVE);

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function deletePaymentByModal(): void
    {
        $this->outputMsg('Delete a payment');

        $this->clickElement(self::FIRST_BUTTON_SHOW);
        sleep(1); // transition delay

        $this->clickElement(self::MODAL_BUTTON_DELETE);
        sleep(1); // transition delay

        $this->clickElement(self::MODAL_BUTTON_CONFIRM);

        $this->client->waitFor(self::ALERT_WARNING);
        $this->assertSelectorExists(self::ALERT_WARNING);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }

    private function restorePayment()
    {
        $this->outputMsg('Restore a payment');

        $this->clickElement('button[type="reset"]');
        $this->clickElement('label[for="deleted_deleted"]');
        $this->clickElement('button[id="search"]');

        $this->client->waitFor('table');
        $this->clickElement(self::FIRST_BUTTON_RESTORE);

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client->quit();
    }
}
