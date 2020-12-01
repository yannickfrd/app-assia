<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Tinify\Tinify;

class FileUploader
{
    protected $targetDirectory;
    protected $slugger;
    protected $tinify;

    public function __construct(string $targetDirectory, Tinify $tinify, string $tinifyKey)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = new AsciiSlugger();
        $this->tinify = $tinify;
        $this->tinify->setKey($tinifyKey);
    }

    /**
     * Télécharge un fichier.
     */
    public function upload(UploadedFile $file, string $path = null): string
    {
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // $filename = str_replace([' ', '/'], '-', $filename);
        // $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_-] remove; Lower()', $filename);
        $slug = $this->slugger->slug($filename);
        $newFilename = $slug.'-'.uniqid().'.'.$file->guessExtension();
        // $extensionFile = $file->guessExtension();

        // Move the file to the directory where document are stored
        try {
            $file->move(
                $this->getTargetDirectory().$path,
                $newFilename
            );
        } catch (FileException $e) {
            throw new \Exception("Une erreur s'est produite lors du téléchargement du document : ".$e);
        }

        $this->compressImage($path, $newFilename);

        return $newFilename;
    }

    /**
     * Compresse le fichier si c'est une image.
     */
    public function compressImage(string $path, string $newFilename): void
    {
        $imageExtensions = ['jpg', 'jpeg', 'png'];
        $pathFile = $this->targetDirectory.$path.'/'.$newFilename;
        $pathParts = pathinfo($pathFile);

        if (in_array($pathParts['extension'], $imageExtensions) && $this->tinify->getCompressionCount() < 450) {
            $source = \Tinify\fromFile($pathFile);
            $source->toFile($pathFile);
        }
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
