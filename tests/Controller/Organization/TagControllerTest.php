<?php

namespace App\Tests\Controller\Organization;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TagControllerTest extends WebTestCase
{
    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    private $fixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->followRedirects();

        /** @var AbstractDatabaseTool */
        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/tag_fixtures_test.yaml',
        ]);
    }

    public function testTagIndexPageIsUpWithRoleAdmin(): void
    {
        $this->client->loginUser($this->fixtures['user_admin']);

        $this->client->request('GET', '/admin/tags');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Étiquettes');
    }

    public function testTagIndexPageIsForbiddenWithRoleUser(): void
    {
        $this->client->loginUser($this->fixtures['john_user']);

        $this->client->request('GET', '/admin/tags');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @dataProvider provideBadUser
     */
    public function testCreateTagWithBadUserRole(string $user): void
    {
        $this->client->loginUser($this->fixtures[$user]);

        $this->client->request('GET', '/admin/tag/new');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreateTagIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $crawler = $this->client->request('GET', '/admin/tag/new');

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="tag"]')->form([
            'tag[name]' => 'Tag de test',
        ]);
        $this->client->submit($form);

        $this->assertSelectorTextContains('html', 'L\'étiquette est créée');
    }

    public function testCreateTagBlank(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $crawler = $this->client->request('GET', '/admin/tag/new');

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="tag"]')->form([
            'tag[name]' => '',
        ]);
        $this->client->submit($form);

        $this->assertSelectorTextContains('html', 'Cette valeur ne doit pas être vide');
    }

    /**
     * @dataProvider provideBadUser
     */
    public function testEditTagWithBadUserRole(string $user): void
    {
        $this->client->loginUser($this->fixtures[$user]);

        $this->client->request('GET', '/admin/tag/1/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEditTagIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $crawler = $this->client->request('GET', '/admin/tag/1/edit');

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="tag"]')->form([
            'tag[name]' => 'Tag 123',
        ]);
        $this->client->submit($form);

        $this->assertSelectorTextContains('.alert.alert-success', 'Les modifications sont enregistrées');
    }

    public function testDeleteTagIsSuccessful(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $this->client->request('GET', '/admin/tag/2/delete');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-success', 'L\'étiquette est supprimée');
    }

    public function testDeleteTagFail(): void
    {
        $this->client->loginUser($this->fixtures['user_super_admin']);

        $this->client->request('GET', '/admin/tag/'.'fail'.'/delete');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function provideBadUser(): \Generator
    {
        yield ['john_user'];
        yield ['user_admin'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
