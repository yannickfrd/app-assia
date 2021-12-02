<?php

namespace App\Service\File;

use Tinify\Tinify;

/**
 * CLass to compress and optimize image files with Tinify API.
 */
class ImageOptimizer
{
    protected $tinify;
    protected $tinifyKey;
    protected $file;
    protected $fileName;
    protected $fileExtension;
    protected $toFolder;
    protected $originalFile;
    protected $optimizedFile;

    public function __construct(Tinify $tinify, string $tinifyKey)
    {
        $this->tinify = $tinify;
        $this->tinifyKey = $tinifyKey;
        $this->init();
    }

    private function init(): void
    {
        try {
            $this->tinify->setKey($this->tinifyKey);
        } catch (\Tinify\AccountException $e) {
            echo 'The error message is: '.$e->getMessage();
            // Verify your API key and account limit.
        } catch (\Tinify\ClientException $e) {
            // Check your source image and request options.
        } catch (\Tinify\ServerException $e) {
            // Temporary issue with the Tinify API.
        } catch (\Tinify\ConnectionException $e) {
            // A network connection error occurred.
        } catch (\Exception $e) {
            // Something else went wrong, unrelated to the Tinify API.
        }
    }

    /**
     * Compresse l'image.
     */
    public function compressImage(string $file): ?int
    {
        try {
            $source = \Tinify\fromFile($file);
            return $source->toFile($file);
        } catch (\Exception $e) {
            return false;
        }
        // move_uploaded_file($this->file['tmp_name'], $this->toFolder.$this->originalFile);
        // $source = \Tinify\fromFile($this->toFolder.$this->originalFile);
        // $this->optimizedFile = $this->fileName.'-optimized.'.$this->fileExtension;
        // $newfile = $source->toFile($this->toFolder.$this->optimizedFile);
    }

    /**
     * Resize the image.
     */
    public function resizeImage(string $file, string $method, int $width, int $height): string
    {
        if (!$this->optimizedFile) {
            $this->compressImage($file);
        }
        // Method : fit (ex 800x450), cover (ex: 800x450), thumb (ex: 150x150)
        $source = \Tinify\fromFile($this->toFolder.$this->optimizedFile);
        $resized = $source->resize([
            'method' => $method,
            'width' => $width,
            'height' => $height,
        ]);
        $resized->toFile($this->toFolder.$this->fileName.'-'.$method.'-'.$width.'x'.$height.'.'.$this->fileExtension);

        return "L'image est redimesionnée avec la méthode \"".$method.'".';
    }

    /**
     * Create an icon.
     */
    public function createIcon($toFolder): string
    {
        if (!$this->optimizedFile) {
            $this->compressImage($this->toFolder.$this->optimizedFile);
        }
        $source = \Tinify\fromFile($this->toFolder.$this->optimizedFile);
        $resized = $source->resize([
            'method' => 'thumb',
            'width' => 64,
            'height' => 64,
        ]);
        $resized->toFile($toFolder);

        return 'Le nouveau logo est enregistré.';
    }

    public function preserveMetadata(): string
    {
        if (!$this->optimizedFile) {
            $this->compressImage($this->toFolder.$this->optimizedFile);
        }
        $source = \Tinify\fromFile($this->toFolder.$this->optimizedFile);
        $copyrighted = $source->preserve('copyright', 'creation');
        $copyrighted->toFile($this->toFolder.$this->optimizedFile);

        return 'Les métadonnées sont récupérées.';
    }

    /**
     * Give the number of compressions in this month.
     */
    public function compressionCount(): string
    {
        $compressionsThisMonth = \Tinify\compressionCount();

        return $compressionsThisMonth.'/500 compressions réalisées au cours du mois.';
    }
}
