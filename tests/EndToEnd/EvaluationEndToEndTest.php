<?php

namespace App\Tests\EndToEnd;

use App\Tests\EndToEnd\Traits\AppPantherTestTrait;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class EvaluationEndToEndTest extends PantherTestCase
{
    use AppPantherTestTrait;

    public const BUTTON_SHOW = 'a.calendar-event';

    public const FORM_RDV = 'form[name="evaluation"]';
    public const BUTTON_SAVE = 'button[name="send"]';
    public const BUTTON_DELETE = 'a#modal-btn-delete';

    public const MSG_FLASH = '#js-msg-flash';
    public const BUTTON_CLOSE_MSG = '#btn-close-msg';
    public const ALERT_SUCCESS = '.alert.alert-success';
    public const ALERT_WARNING = '.alert.alert-warning';

    protected Client $client;

    /** @var \Faker\Generator */
    protected $faker;

    protected function setUp(): void
    {
        $this->client = $this->loginUser();
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testEvaluation(): void
    {
        $this->client->request('GET', '/support/1/show');

        $this->showEvaluation();
        $this->editEvaluation();

        $this->client->quit();
    }

    private function showEvaluation(): void
    {
        $this->outputMsg('Show the evaluation page');

        $this->clickElement('a#support-evaluation');

        $this->assertSelectorTextContains('h1', 'Évaluation sociale');

        $this->fixScrollBehavior();
    }

    private function editEvaluation(): void
    {
        $this->outputMsg('Edit the evaluation');

        $this->clickElement('#card-evalHousing');
        sleep(1);
        $this->clickElement('#card-evalHousing button[type="submit"]');

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);

        $this->clickElement(self::BUTTON_SAVE);

        $this->client->waitFor(self::ALERT_SUCCESS);
        $this->assertSelectorExists(self::ALERT_SUCCESS);

        $this->clickElement(self::BUTTON_CLOSE_MSG);
    }
}
