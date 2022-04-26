<?php

namespace App\Service\File;

use App\Entity\Support\Document;
use App\Entity\Support\SupportGroup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Service to upload a file.
 */
class FileUploader
{
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    protected $em;
    protected $targetDirectory;
    protected $optimizer;
    protected $slugger;

    public function __construct(
        EntityManagerInterface $em,
        ImageOptimizer $optimizer,
        SluggerInterface $slugger,
        string $targetDirectory
        ) {
        $this->em = $em;
        $this->optimizer = $optimizer;
        $this->slugger = $slugger;
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * Upload le fichier  et crée le document associé.
     *
     * @param UploadedFile[]|UploadedFile $files
     */
    public function createDocuments(SupportGroup $supportGroup, $files): array
    {
        $peopleGroup = $supportGroup->getPeopleGroup();
        $now = new \DateTime();

        /** @var Document[] */
        $documents = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }
            $path = $now->format('Y/m/d/').$peopleGroup->getId().'/';
            $fileName = $this->upload($file, $path);
            $size = \filesize($this->getTargetDirectory().$path.'/'.$fileName);

            $document = (new Document())
                ->setName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                ->setInternalFileName($fileName)
                ->setSize($size)
                ->setPeopleGroup($peopleGroup)
                ->setSupportGroup($supportGroup);

            $this->em->persist($document);

            $documents[] = $document;
        }

        $this->em->flush();

//        $data = [];
//        $names = [];
//        foreach ($documents as $document) {
//            $data[] = $this->normalizer->normalize($document, 'json', ['groups' => ['show_document', 'view']]);
//            $names[] = $document->getName();
//        }

        return [
            'action' => 'create',
            'alert' => 'success',
            'documents' => $documents,
        ];
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
            $this->optimizer->compressImage($this->targetDirectory.$path.'/'.$filename);
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

        return $slug.'.'.$file->guessExtension();

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
