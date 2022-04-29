<?php

namespace App\Service\Document;

use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\DocumentSearch;
use App\Form\Model\Support\SupportDocumentSearch;
use App\Repository\Support\DocumentRepository;
use App\Service\Pagination;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class DocumentPaginator
{
    private $pagination;
    private $documentRepo;

    /** @var User */
    private $user;

    public function __construct(Pagination $pagination, DocumentRepository $documentRepo, Security $security)
    {
        $this->pagination = $pagination;
        $this->documentRepo = $documentRepo;
        $this->user = $security->getUser();
    }

    /**
     * Donne les tâches du suivi.
     *
     * @param DocumentSearch|SupportDocumentSearch $search
     */
    public function paginate(Request $request, $search, ?SupportGroup $supportGroup = null): PaginationInterface
    {
        if ($supportGroup) {
            return $this->pagination->paginate(
                $this->documentRepo->findSupportDocumentsQuery($search, $supportGroup),
                $request
            );
        }

        return $this->pagination->paginate(
            $this->documentRepo->findDocumentsQuery($search, $this->user),
            $request
        );
    }

    public function getDocumentRepository(): DocumentRepository
    {
        return $this->documentRepo;
    }
}
