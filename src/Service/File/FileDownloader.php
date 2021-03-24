<?php

namespace App\Service\File;

use App\Entity\Support\SupportGroup;
use App\Repository\Support\DocumentRepository;

class FileDownloader
{
    private $repoDocument;
    private $download;
    private $downloadsDirectory;
    private $documentsDirectory;

    public function __construct(DocumentRepository $repoDocument, Download $download, string $downloadsDirectory, string $documentsDirectory)
    {
        $this->repoDocument = $repoDocument;
        $this->download = $download;
        $this->downloadsDirectory = $downloadsDirectory;
        $this->documentsDirectory = $documentsDirectory;
    }

    public function send(array $idDocuments, SupportGroup $supportGroup)
    {
        $now = new \DateTime();

        $documents = $this->repoDocument->findDocumentsById($idDocuments, $supportGroup);
        $path = $this->downloadsDirectory.$now->format('Y/m/d/').$supportGroup->getId().'/';

        if (!file_exists($path)) {
            mkdir($path, 0700, true);
        }

        $zip = new \ZipArchive();
        $zipFile = $path.$now->format('Y_m_d_').'documents_'.uniqid().'.zip';

        if (!$zip->open($zipFile, \ZipArchive::CREATE)) {
            throw new \Exception('The zip file could not opened.');
        }

        foreach ($documents as $document) {
            $path = $this->documentsDirectory.$document->getCreatedAt()->format('Y/m/d/').$document->getPeopleGroup()->getId().'/';
            $filename = $document->getInternalFileName();
            $file = $path.$filename;
            $zip->addFile($file, $filename);
        }
        $zip->close();

        return $this->download->send($zipFile);
    }

    public function getDownloadDirectory()
    {
        return $this->downloadsDirectory;
    }

    public function getDocumentsDirectory()
    {
        return $this->documentsDirectory;
    }
}
