<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file, $path = null)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // this is needed to safely include the file name as part of the URL
        $safeFilename = transliterator_transliterate("Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()", $originalFilename);
        $newFilename = $safeFilename . "-" . uniqid() . "." . $file->guessExtension();

        // Move the file to the directory where document are stored
        try {
            $file->move(
                $this->getTargetDirectory() . $path,
                $newFilename
            );
        } catch (FileException $e) {
            throw new \Exception("Une erreur s'est produite lors du téléchargement du document : " . $e);
        }

        return $newFilename;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
