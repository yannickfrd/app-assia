<?php

namespace App\Service\File;

use App\Entity\Support\SupportGroup;
use App\Repository\Support\DocumentRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileDownloader extends Downloader
{
    private $documentRepo;
    private $downloadsDirectory;
    private $documentsDirectory;

    public function __construct(
        DocumentRepository $documentRepo,
        string $downloadsDirectory,
        string $documentsDirectory,
        string $appEnv
    ) {
        $this->documentRepo = $documentRepo;
        $this->downloadsDirectory = $downloadsDirectory;
        $this->documentsDirectory = $documentsDirectory;

        parent::__construct($appEnv);
    }

    public function sendDocuments(array $documentIds, SupportGroup $supportGroup): StreamedResponse
    {
        $now = new \DateTime();

        $documents = $this->documentRepo->findDocumentsById($documentIds, $supportGroup);
        $path = $this->downloadsDirectory.$now->format('Y/m/d/').$supportGroup->getId().'/';

        if (!file_exists($path)) {
            mkdir($path, 0700, true);
        }

        $zip = new \ZipArchive();
        $zipFile = $path.$now->format('Y_m_d_').'documents_'.$supportGroup->getId().$now->format('_His').'.zip';

        if (!$zip->open($zipFile, \ZipArchive::CREATE)) {
            throw new \Exception('The zip file could not opened.');
        }

        foreach ($documents as $document) {
            $path = $this->documentsDirectory.$document->getPath();
            $filename = $document->getInternalFileName();
            $file = $path.$filename;
            if (file_exists($file)) {
                $zip->addFile($file, $filename);
            }
        }
        $zip->close();

        return $this->send($zipFile, ['action-type' => 'download']);
    }

    public function getDownloadDirectory(): string
    {
        return $this->downloadsDirectory;
    }

    public function getDocumentsDirectory(): string
    {
        return $this->documentsDirectory;
    }
}
