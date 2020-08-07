<?php

namespace App\Controller;

use App\Service\Pagination;
use App\Repository\AvdlRepository;
use App\Form\Model\AvdlSupportSearch;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SupportGroupRepository;
use App\Form\Support\AvdlSupportSearchType;
use App\Controller\Traits\ErrorMessageTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AvdlController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;
    private $repoSupportGroup;
    private $repo;
    private $serviceId;

    public function __construct(EntityManagerInterface $manager, SupportGroupRepository $repoSupportGroup, AvdlRepository $repo)
    {
        $this->manager = $manager;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repo = $repo;
        $this->serviceId = 5;
    }

    /**
     * Liste des suivis AVDL.
     *
     * @Route("/avdl-supports", name="avdl_supports", methods="GET|POST")
     */
    public function viewListAvdlSupports(Request $request, AvdlSupportSearch $search = null, Pagination $pagination): Response
    {
        $search = (new AvdlSupportSearch())->setStatus([2]);

        $form = ($this->createForm(AvdlSupportSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            // return $this->exportData($search);
        }

        return $this->render('app/avdl/listAvdlSupports.html.twig', [
            'supportGroupSearch' => $search,
            'form' => $form->createView(),
            'supports' => $pagination->paginate($this->repoSupportGroup->findAllAvdlSupportsQuery($search, $this->serviceId), $request),
        ]);
    }
}
