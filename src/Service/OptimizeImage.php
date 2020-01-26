<?php

namespace App\Service;

use Tinify\Tinify;

// Une classe pour compresser et optimiser les images avec Tinify
class OptimizeImage
{
    private $tinify,
        $file,
        $fileName,
        $fileExtension,
        $fileSize,
        $toFolder,
        $originalFile,
        $optimizedFile;

    public function __construct(Tinify $tinify)
    {
        $this->tinify = $tinify;
        $this->tinify->setKey($_SERVER["TINIFY_KEY"]);
        $this->init();
    }

    private function init()
    {
        try {
            // Use the Tinify API client.
        } catch (\Tinify\AccountException $e) {
            print("The error message is: " . $e->getMessage());
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

    // Compresse l'image
    public function compressImage()
    {
        move_uploaded_file($this->file["tmp_name"], $this->toFolder . $this->originalFile);
        $source = \Tinify\fromFile($this->toFolder . $this->originalFile);
        $this->optimizedFile = $this->fileName . "-optimized." . $this->fileExtension;
        $newfile = $source->toFile($this->toFolder . $this->optimizedFile);
    }

    // Redimmensionne l'image
    public function resizeImage($method, $width, $height)
    {
        if (!$this->optimizedFile) {
            $this->compressImage();
        }
        // Method : fit (ex 800x450), cover (ex: 800x450), thumb (ex: 150x150)
        $source = \Tinify\fromFile($this->toFolder . $this->optimizedFile);
        $resized = $source->resize([
            "method" => $method,
            "width" => $width,
            "height" => $height
        ]);
        $resized->toFile($this->toFolder . $this->fileName . "-" . $method . "-" . $width . "x" . $height . "." . $this->fileExtension);
        return "L'image a été redimesionnée avec la méthode \"" . $method . "\".";
    }

    // Créé une icone
    public function createIcon($toFolder)
    {
        if (!$this->optimizedFile) {
            $this->compressImage();
        }
        $source = \Tinify\fromFile($this->toFolder . $this->optimizedFile);
        $resized = $source->resize(array(
            "method" => "thumb",
            "width" => 64,
            "height" => 64
        ));
        $resized->toFile($toFolder);
        return "Le nouveau logo a été enregistré.";
    }

    // Récupère les métadonnées
    public function preserveMetadata()
    {
        if (!$this->optimizedFile) {
            $this->compressImage();
        }
        $source = \Tinify\fromFile($this->toFolder . $this->optimizedFile);
        $copyrighted = $source->preserve("copyright", "creation");
        $copyrighted->toFile($this->toFolder . $this->optimizedFile);
        return "Les métadonnées ont été récupérées.";
    }

    // Donne le nombre de compressions réalisées au cours du mois
    public function compressionCount()
    {
        $compressionsThisMonth = \Tinify\compressionCount();
        return $compressionsThisMonth . "/500 compressions réalisées au cours du mois.";
    }
}
