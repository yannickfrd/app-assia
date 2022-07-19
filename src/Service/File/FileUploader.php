<?php

namespace App\Service\File;

use App\Entity\Support\Document;
use App\Entity\Support\SupportGroup;
use App\Repository\Support\DocumentRepository;
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
    protected $documentRepo;
    protected $documentsDirectory;
    protected $optimizer;
    protected $slugger;

    public function __construct(
        EntityManagerInterface $em,
        DocumentRepository $documentRepo,
        ImageOptimizer $optimizer,
        SluggerInterface $slugger,
        string $documentsDirectory
    ) {
        $this->em = $em;
        $this->documentRepo = $documentRepo;
        $this->optimizer = $optimizer;
        $this->slugger = $slugger;
        $this->documentsDirectory = $documentsDirectory;
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
            $fileName = $this->normalizeFilename($file);

            $document = (new Document())
                ->setName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                ->setInternalFileName($fileName)
                ->setSize($file->getSize())
                ->setPeopleGroup($peopleGroup)
                ->setSupportGroup($supportGroup)
            ;

            if (false === $this->checkDocumentExists($document)) {
                $this->upload($file, $path);

                $this->em->persist($document);
            }

            $documents[] = $document;
        }

        $this->em->flush();

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
            $file->move($this->getDocumentsDirectory().$path, $filename);
        } catch (FileException $e) {
            throw new \Exception("Une erreur s'est produite lors du téléchargement du document : ".$e->getMessage());
        }

        if ($this->checkIfImage($path, $filename)) {
            $this->optimizer->compressImage($this->documentsDirectory.$path.'/'.$filename);
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
    }

    /**
     * Check if the file is an image.
     */
    protected function checkIfImage(string $path, string $newFilename): bool
    {
        $pathFile = $this->documentsDirectory.$path.'/'.$newFilename;
        $pathParts = pathinfo($pathFile);

        return in_array($pathParts['extension'], self::IMAGE_EXTENSIONS);
    }

    public function getDocumentsDirectory()
    {
        return $this->documentsDirectory;
    }

    protected function checkDocumentExists(Document $document): bool
    {
        return 0 !== $this->documentRepo->count([
            'supportGroup' => $document->getSupportGroup(),
            'internalFileName' => $document->getInternalFileName(),
            'size' => $document->getSize(),
        ]);
    }
}
