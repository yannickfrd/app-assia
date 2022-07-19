<?php

declare(strict_types=1);

namespace App\Tests\Service\Admin;

use App\Entity\Event\Rdv;
use App\Entity\Support\Note;
use App\Entity\Admin\Setting;
use App\Entity\Event\Task;
use App\Entity\People\Person;
use App\Entity\Support\Payment;
use App\Service\Admin\Archiver;
use App\Entity\Support\Document;
use App\Entity\People\PeopleGroup;
use App\Entity\Organization\Service;
use App\Entity\Support\SupportGroup;
use App\Service\SupportGroup\SupportAnonymizer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class ArchiverTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $this->fixtures = $this->databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/document_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/rdv_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/note_fixtures_test.yaml',
            dirname(__DIR__).'/../fixtures/payment_fixtures_test.yaml',
        ]);
    }

    public function testGetDatasIsSuccessful(): void
    {
        $stats= $this->getArchiver()->getStats();

        $this->getAllAsserts($stats);
    }

    public function testArchiveIsSuccessful(): void
    {
        $manager = $this->getArchiver();

        $manager->archive();

        $stats = $manager->getStats();

        $this->getSoftAsserts($stats, 0);
        $this->getHardAsserts($stats);
    }

    public function testPurgeIsSuccessful(): void
    {
        $manager = $this->getArchiver();

        $manager->purge();

        $stats = $manager->getStats();

        $this->getSoftAsserts($stats);
        $this->getHardAsserts($stats, 0);
    }

    private function getAllAsserts(array $stats, int $nbAssertEquals = 1)
    {
        $this->getSoftAsserts($stats, $nbAssertEquals);
        $this->getHardAsserts($stats, $nbAssertEquals);
    }

    private function getSoftAsserts(array $stats, int $nbAssertEquals = 1)
    {
        $this->assertEquals($nbAssertEquals, $stats['support_groups']['archive_count']);
        $this->assertEquals($nbAssertEquals, $stats['people_groups']['archive_count']);
        $this->assertEquals($nbAssertEquals, $stats['support_people']['archive_count']);
        $this->assertEquals($nbAssertEquals, $stats['documents']['archive_count']);
        $this->assertEquals($nbAssertEquals, $stats['rdvs']['archive_count']);
        $this->assertEquals($nbAssertEquals, $stats['notes']['archive_count']);
        $this->assertEquals($nbAssertEquals, $stats['payments']['archive_count']);
    }

    private function getHardAsserts(array $stats, int $nbAssertEquals = 1)
    {
        $this->assertEquals($nbAssertEquals, $stats['support_groups']['purge_count']);
        $this->assertEquals($nbAssertEquals, $stats['people_groups']['purge_count']);
        $this->assertEquals($nbAssertEquals, $stats['support_people']['purge_count']);
        $this->assertEquals($nbAssertEquals, $stats['documents']['purge_count']);
        $this->assertEquals($nbAssertEquals, $stats['rdvs']['purge_count']);
        $this->assertEquals($nbAssertEquals, $stats['notes']['purge_count']);
        $this->assertEquals($nbAssertEquals, $stats['payments']['purge_count']);
    }

    private function getArchiver(): Archiver
    {
        $kernel = self::bootKernel();

        return new Archiver(
            $em = $kernel->getContainer()->get('doctrine')->getManager(),
            $em->getRepository(Document::class),
            $em->getRepository(Note::class),
            $em->getRepository(PeopleGroup::class),
            $em->getRepository(Person::class),
            $em->getRepository(Rdv::class),
            $em->getRepository(Payment::class),
            $em->getRepository(Service::class),
            $em->getRepository(Setting::class),
            $em->getRepository(SupportGroup::class),
            $em->getRepository(Task::class),
            new SupportAnonymizer(),
            dirname(__DIR__).'/../../public/uploads/documents/'
        );
    }
}
