<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Service to upload a file.
 */
class FileUploader
{
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    protected $targetDirectory;
    protected $optimizeImage;
    protected $slugger;

    public function __construct(string $targetDirectory, OptimizeImage $optimizeImage)
    {
        $this->targetDirectory = $targetDirectory;
        $this->optimizeImage = $optimizeImage;
        $this->slugger = new AsciiSlugger();
    }

    /**
     * Download a file.
     */
    public function upload(UploadedFile $file, string $path = null): string
    {
        $filename = $this->normalizeFilename($file);
        // Move the file to the directory where document are stored
        try {
            $file->move($this->getTargetDirectory().$path, $filename);
        } catch (FileException $e) {
            throw new \Exception("Une erreur s'est produite lors du téléchargement du document : ".$e->getMessage());
        }

        if ($this->checkIfImage($path, $filename)) {
            $this->optimizeImage->compressImage($this->targetDirectory.$path.'/'.$filename);
        }

        return $filename;
    }

    /**
     * Normalize the filename.
     */
    protected function normalizeFilename(UploadedFile $file): string
    {
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = $this->slugger->slug($filename);

        return $slug.'-'.uniqid().'.'.$file->guessExtension();

        // $filename = str_replace([' ', '/'], '-', $filename);
        // $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_-] remove; Lower()', $filename);
        // $extensionFile = $file->guessExtension();
    }

    /**
     * Check if the file is an image.
     */
    protected function checkIfImage(string $path, string $newFilename): bool
    {
        $pathFile = $this->targetDirectory.$path.'/'.$newFilename;
        $pathParts = pathinfo($pathFile);

        return in_array($pathParts['extension'], self::IMAGE_EXTENSIONS);
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
