<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tinify\Tinify;

class FileUploader
{
    private $targetDirectory;
    private $tinifyKey;

    public function __construct($targetDirectory, $tinifyKey)
    {
        $this->targetDirectory = $targetDirectory;
        $this->tinifyKey = $tinifyKey;
    }

    public function upload(UploadedFile $file, $path = null)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $originalFilename = str_replace(" ", "-", $originalFilename);
        $safeFilename = transliterator_transliterate("Any-Latin; Latin-ASCII; [^A-Za-z0-9_-] remove; Lower()", $originalFilename);
        $newFilename = $safeFilename . "-" . uniqid() . "." . $file->guessExtension();
        $extensionFile = $file->guessExtension();

        // Move the file to the directory where document are stored
        try {
            $file->move(
                $this->getTargetDirectory() . $path,
                $newFilename
            );
        } catch (FileException $e) {
            throw new \Exception("Une erreur s'est produite lors du téléchargement du document : " . $e);
        }

        $this->compressImage($path, $newFilename);

        return $newFilename;
    }

    public function compressImage($path, $newFilename)
    {
        $imageExtensions = ["jpg", "jpeg", "png"];
        $pathFile = $this->getTargetDirectory() . $path . "/" . $newFilename;
        $pathParts = pathinfo($pathFile);

        if (in_array($pathParts["extension"], $imageExtensions)) {
            \Tinify\setKey($this->tinifyKey);
            $source = \Tinify\fromFile($pathFile);
            $source->toFile($pathFile);
        }
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
