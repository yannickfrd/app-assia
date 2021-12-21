<?php

namespace App\Tests\Controller;

use App\Entity\Organization\Tag;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TagControllerTest extends WebTestCase
{
    use AppTestTrait;

    /** @var KernelBrowser */
    protected $client;

    /** @var array */
    private $data;

    /** @var Tag */
    private $tag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();

        /* @var AbstractDatabaseTool */
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/TagFixturesTest.yaml',
        ]);

        $this->tag = $this->fixtures['tag1'];
    }

    public function testListTagsIsSuccessfully(): void
    {
        $this->createLogin($this->fixtures['userAdmin']);
        $this->client->request('GET', '/admin/tags');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Étiquettes');
    }

    public function testListTagsWithRoleUser(): void
    {
        $this->createLogin($this->fixtures['userRoleUser']);
        $this->client->request('GET', '/admin/tags');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @dataProvider provideBadUser
     */
    public function testCreateTagsWithBadUserRole(string $user): void
    {
        $this->createLogin($this->fixtures[$user]);
        $this->client->request('GET', '/admin/tag/new');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @dataProvider provideBadUser
     */
    public function testEditTagsWithBadUserRole(string $user): void
    {
        $this->createLogin($this->fixtures[$user]);
        $this->client->request('GET', '/admin/tags/'.$this->tag->getId().'/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testCreateTagIsSuccessfully(): void
    {
        $this->createLogin($this->fixtures['userSuperAdmin']);
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
        $this->createLogin($this->fixtures['userSuperAdmin']);
        $crawler = $this->client->request('GET', '/admin/tag/new');

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="tag"]')->form([
            'tag[name]' => '',
        ]);
        $this->client->submit($form);

        $this->assertSelectorTextContains('html', 'Cette valeur ne doit pas être vide');
    }

    public function testEditTagIsSuccessfully(): void
    {
        $this->createLogin($this->fixtures['userSuperAdmin']);
        $crawler = $this->client->request('GET', '/admin/tags/'.$this->tag->getId().'/edit');

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="tag"]')->form([
            'tag[name]' => 'Tag 123',
        ]);
        $this->client->submit($form);

        $this->assertSelectorTextContains('html', 'Les modifications sont enregistrées');
    }

    public function testDeleteTagIsSuccessfully(): void
    {
        $this->createLogin($this->fixtures['userSuperAdmin']);
        $this->client->request('GET', '/admin/tags/'.$this->tag->getId().'/delete');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('html', 'L\'étiquette est supprimée');
    }

    public function testDeleteTagFail(): void
    {
        $this->createLogin($this->fixtures['userSuperAdmin']);
        $this->client->request('GET', '/admin/tags/'.'fail'.'/delete');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function provideBadUser(): \Generator
    {
        yield ['userRoleUser'];
        yield ['userAdmin'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
        $this->fixtures = null;
        $this->tag = null;
    }
}
