<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Admin\DatabaseBackup;
use App\Repository\Admin\DatabaseBackupRepository;
use App\Service\DatabaseDumper;
use App\Service\File\Downloader;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DatabaseBackupController extends AbstractController
{
    use ErrorMessageTrait;

    protected $em;
    protected $databaseBackupRepo;

    public function __construct(EntityManagerInterface $em, DatabaseBackupRepository $databaseBackupRepo)
    {
        $this->em = $em;
        $this->databaseBackupRepo = $databaseBackupRepo;
    }

    /**
     * Sauvegardes de la base de données.
     *
     * @Route("/admin/database-backups", name="database_backup_index", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function index(Request $request, Pagination $pagination): Response
    {
        return $this->render('app/admin/backup/backup_database.html.twig', [
            'backups' => $pagination->paginate($this->databaseBackupRepo->findBackupsQuery(), $request, 10),
        ]);
    }

    /**
     * Créer une sauvegarde la base de données.
     *
     * @Route("/admin/database-backup/create", name="database_backup_create")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function create(DatabaseDumper $databaseDumper): Response
    {
        $backupDatas = $databaseDumper->dump();

        $databaseBackup = (new DatabaseBackup())
            ->setSize($backupDatas['size'])
            ->setFileName($backupDatas['fileName'])
            ->setPath($backupDatas['path']);

        $this->em->persist($databaseBackup);
        $this->em->flush();

        $this->addFlash('success', 'La sauvegarde de la base de données est créée.');

        return $this->redirectToRoute('database_backup_index');
    }

    /**
     * Donne le fichier de sauvegarde la base de données.
     *
     * @Route("/admin/database-backup/{id}/download", name="database_backup_download", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function download(DatabaseBackup $databaseBackup, Downloader $downloader): Response
    {
        if (file_exists($databaseBackup->getPath())) {
            return $downloader->send($databaseBackup->getPath());
        }

        $this->addFlash('danger', 'Ce fichier n\'existe pas.');

        return $this->redirectToRoute('database_backup_index');
    }

    /**
     * Supprime le fichier de sauvegarde de la base de données.
     *
     * @Route("/admin/database-backup/{id}/delete", name="database_backup_delete", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function delete(DatabaseBackup $databaseBackup): Response
    {
        if (file_exists($databaseBackup->getPath())) {
            unlink($databaseBackup->getPath());
        }

        $this->em->remove($databaseBackup);
        $this->em->flush();

        $this->addFlash('warning', 'La sauvegarde de la base de données est supprimée.');

        return $this->redirectToRoute('database_backup_index');
    }
}
