<?php

namespace App\Controller;

use App\Service\Download;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\DatabaseBackup;
use App\Form\DatabaseBackup\BackupSearchType;
use App\Form\Model\BackupSearch;
use App\Repository\DatabaseBackupRepository;
use App\Service\DumpDatabase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DatabaseBackupController extends AbstractController
{
    use ErrorMessageTrait;

    protected $manager;
    protected $repo;

    public function __construct(EntityManagerInterface $manager, DatabaseBackupRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Sauvegardes de la base de données.
     *
     * @Route("admin/database-backups", name="database_backups", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function listBackups(Request $request, BackupSearch $search = null, Pagination $pagination): Response
    {
        $search = new BackupSearch();

        $form = ($this->createForm(BackupSearchType::class, $search))
            ->handleRequest($request);

        return $this->render('app/backupDatabase/backupDatabase.html.twig', [
            'form' => $form->createView(),
            'backups' => $pagination->paginate($this->repo->findBackupsQuery(), $request, 10) ?? null,
        ]);
    }

    /**
     * Créer une sauvegarde la base de données.
     *
     * @Route("admin/database-backup/create", name="database_backup_create")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function createBackup(DumpDatabase $dumpDatabase): Response
    {
        $backupDatas = $dumpDatabase->dump();

        $databaseBackup = (new DatabaseBackup())
            ->setSize($backupDatas['size'])
            ->setZipSize($backupDatas['zipSize'])
            ->setFileName($backupDatas['fileName']);

        $this->manager->persist($databaseBackup);
        $this->manager->flush();

        $this->addFlash('success', 'La sauvegarde de la base de données est créée.');

        return $this->redirectToRoute('database_backups');
    }

    /**
     * Donne le fichier de sauvegarde la base de données.
     *
     * @Route("admin/database-backup/{id}/get", name="database_backup_get", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function getDatabaseBackup(DatabaseBackup $databaseBackup, Download $download): Response
    {
        $path = $this->getPathDatabaseBackup($databaseBackup);

        if (file_exists($path.$databaseBackup->getFileName())) {
            return $download->send($path.$databaseBackup->getFileName());
        }

        $this->addFlash('danger', 'Ce fichier n\'existe pas.');

        return $this->redirectToRoute('database_backups');
    }

    /**
     * Supprime le fichier de sauvegarde de la base de données.
     *
     * @Route("admin/database-backup/{id}/delete", name="database_backup_delete", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function deleteDatabase(DatabaseBackup $databaseBackup): Response
    {
        $path = $this->getPathDatabaseBackup($databaseBackup);

        if (file_exists($path.$databaseBackup->getFileName())) {
            unlink($path.$databaseBackup->getFileName());
        }

        $this->manager->remove($databaseBackup);
        $this->manager->flush();

        $this->addFlash('warning', 'La sauvegarde de la base de données est supprimée.');

        return $this->redirectToRoute('database_backups');
    }

    protected function getPathDatabaseBackup(DatabaseBackup $databaseBackup)
    {
        return 'backups/'.$databaseBackup->getCreatedAt()->format('Y/m/d/');
    }
}
