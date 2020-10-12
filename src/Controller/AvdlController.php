<?php

namespace App\Controller;

use App\Entity\Service;
use App\Service\Pagination;
use App\Entity\SupportGroup;
use App\Form\Model\AvdlSupportSearch;
use App\Export\AvdlSupportPersonExport;
use App\Form\Avdl\AvdlSupportSearchType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SupportGroupRepository;
use App\Repository\SupportPersonRepository;
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
    private $repoSupportPerson;
    private $serviceId;

    public function __construct(EntityManagerInterface $manager, SupportGroupRepository $repoSupportGroup, SupportPersonRepository $repoSupportPerson)
    {
        $this->manager = $manager;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repoSupportPerson = $repoSupportPerson;
        $this->serviceId = Service::SERVICE_AVDL_ID;
    }

    /**
     * Liste des suivis AVDL.
     *
     * @Route("/avdl-supports", name="avdl_supports", methods="GET|POST")
     */
    public function viewListAvdlSupports(Request $request, Pagination $pagination): Response
    {
        $search = (new AvdlSupportSearch())->setStatus([SupportGroup::STATUS_IN_PROGRESS]);

        $form = ($this->createForm(AvdlSupportSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search);
        }

        return $this->render('app/avdl/listAvdlSupports.html.twig', [
            'supportGroupSearch' => $search,
            'form' => $form->createView(),
            'supports' => $pagination->paginate($this->repoSupportGroup->findAllAvdlSupportsQuery($search, $this->serviceId), $request),
        ]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(AvdlSupportSearch $search)
    {
        $supports = $this->repoSupportPerson->findSupportsFromServiceToExport($search, $this->serviceId);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('avdl_supports');
        }

        return (new AvdlSupportPersonExport())->exportData($supports);
    }
}
