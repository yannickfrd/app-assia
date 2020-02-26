<?php

namespace App\Controller;

use App\Entity\SupportGroup;
use App\Entity\OriginRequest;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SupportGroupRepository;
use App\Repository\OriginRequestRepository;
use App\Form\OriginRequest\OriginRequestType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OriginRequestController extends AbstractController
{
    private $manager;
    private $repoSupportGroup;
    private $repo;

    public function __construct(EntityManagerInterface $manager, SupportGroupRepository $repoSupportGroup, OriginRequestRepository $repo)
    {
        $this->manager = $manager;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repo = $repo;
    }

    /**
     * Modification de la pré-admission
     * 
     * @Route("/support/{id}/originRequest", name="support_originRequest", methods="GET|POST")
     * @param int $id
     * @param Request $request
     * @return Response
     */

    public function editOriginRequest($id, Request $request): Response
    {
        $supportGroup = $this->repoSupportGroup->findSupportById($id);

        $this->denyAccessUnlessGranted("VIEW", $supportGroup);

        $originRequest = $this->repo->findOriginRequest($supportGroup);

        if (!$originRequest) {
            $originRequest = new OriginRequest();
            $originRequest->setSupportGroup($supportGroup);
        }

        $form = $this->createForm(OriginRequestType::class, $originRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateOriginRequest($originRequest);
        }

        return $this->render("app/originRequest/originRequest.html.twig", [
            "support" => $supportGroup,
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Met à jour l'évaluation sociale du groupe
     * 
     * @param OriginRequest $originRequest
     */
    protected function updateOriginRequest(OriginRequest $originRequest)
    {
        $originRequest->getSupportGroup()->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($originRequest);
        $this->manager->flush();

        $this->addFlash("success", "Les informations ont été mises à jour.");
    }
}