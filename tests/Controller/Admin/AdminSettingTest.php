<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Organization\Service;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdminSettingTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    /** @var array */
    protected $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();
        
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    public function testShowAppSetting(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->client->loginUser($fixtures['user_super_admin']);

        $this->client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h2', 'Paramètres');
    }

    public function testSettingPageIsUp(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->client->loginUser($fixtures['user_super_admin']);

        $this->client->request('GET', '/admin/settings');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="setting"]');
    }

    /** @dataProvider provideBadUser */
    public function testSettingPageWithBadRoleUserIsForbidden(string $user): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->client->loginUser($fixtures[$user]);

        $this->client->request('GET', '/admin/settings');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEditSettingIsSuccessful(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->client->loginUser($fixtures['user_super_admin']);

        $this->sendAdminSettingByDefault();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert', 'La configuration est bien enregistrée.');
    }

    public function testServiceGetDefaultSetting(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->client->loginUser($fixtures['user_super_admin']);

        $this->sendAdminSettingByDefault();

        $crawler = $this->client->request('GET', '/service/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('send')->form()['service']['setting'];

        $this->assertSame(14, (int) $form['softDeletionDelay']->getValue());
        $this->assertSame(18, (int) $form['hardDeletionDelay']->getValue());

        $this->assertCheckboxChecked($form['weeklyAlert']->getName());
        $this->assertCheckboxNotChecked($form['dailyAlert']->getName());
    }

    public function testUserGetServiceSetting(): void
    {
        $fixtures = $this->databaseTool->loadAliceFixture($this->getFixtureFiles());
        $this->client->loginUser($fixtures['user_super_admin']);

        $this->sendAdminSettingByDefault();

        /** @var Service $service */
        $service = $fixtures['service1'];

        $this->client->request('GET', '/service/'.$service->getId());
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('send', [
            'service[setting]' => [
                'softDeletionDelay' => 24,
                'hardDeletionDelay' => 36,
                'weeklyAlert' => true,
                'dailyAlert' => false,
            ],
        ]);
        $this->assertResponseIsSuccessful();

        $this->client->loginUser($fixtures['john_user']);
        $crawler = $this->client->request('GET', '/my_profile');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('send3')->form()['user_setting'];
        $this->assertCheckboxChecked($form['weeklyAlert']->getName());
        $this->assertCheckboxNotChecked($form['dailyAlert']->getName());
    }

    private function sendAdminSettingByDefault(): void
    {
        $this->client->request('GET', '/admin/settings');

        $this->client->submitForm('send', [
            'setting' => [
                'organizationName' => 'Assia Test',
                'softDeletionDelay' => 14,
                'hardDeletionDelay' => 18,
                'weeklyAlert' => true,
                'dailyAlert' => false,
            ],
        ]);
    }

    public function provideBadUser(): \Generator
    {
        yield ['john_user'];
        yield ['user_admin'];
    }

    protected function getFixtureFiles(): array
    {
        return [
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
        ];
    }
}
