<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\Entity\People\PeopleGroup;
use App\Service\DoctrineTrait;
use App\Tests\AppTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArchivePurgeControllerTest extends WebTestCase
{
    use AppTestTrait;
    use DoctrineTrait;

    protected ?KernelBrowser $client;
    protected AbstractDatabaseTool $databaseTool;
    protected ?array $fixtures;
    protected PeopleGroup $peopleGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();

        /* @var AbstractDatabaseTool */
        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml'
        ]);
    }

    public function testAccessAdminArchivePurgeIsSuccessful(): void
    {
        $this->createLogin($this->fixtures['user_super_admin']);

        $this->client->request('GET', 'admin/archive-purge');

        $this->assertResponseIsSuccessful();
    }

    public function testAccessAdminArchivesIsSuccessful(): void
    {
        $this->createLogin($this->fixtures['user_super_admin']);

        $this->client->request('GET', 'admin/archives');

        $this->assertResponseIsSuccessful();
    }

    public function testAccessAdminPurgesIsSuccessful(): void
    {
        $this->createLogin($this->fixtures['user_super_admin']);

        $this->client->request('GET', 'admin/purges');

        $this->assertResponseIsSuccessful();
    }

    /** @dataProvider provideBadUser */
    public function testAccessAdminArchivePurgeWithBadRoles(string $user): void
    {
        $this->createLogin($this->fixtures[$user]);

        $this->client->request('GET', 'admin/archive-purge');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /** @dataProvider provideBadUser */
    public function testAccessAdminArchivesWithBadRoles(string $user): void
    {
        $this->createLogin($this->fixtures[$user]);

        $this->client->request('GET', 'admin/archives');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /** @dataProvider provideBadUser */
    public function testAccessAdminPurgesWithBadRoles(string $user): void
    {
        $this->createLogin($this->fixtures[$user]);

        $this->client->request('GET', 'admin/purges');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function provideBadUser(): \Generator
    {
        yield ['john_user'];
        yield ['user_admin'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;
        $this->fixtures = null;
    }
}
