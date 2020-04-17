<?php

namespace App\Controller;

use App\Entity\OriginRequest;
use App\Entity\SupportGroup;
use App\Form\OriginRequest\OriginRequestType;
use App\Repository\OriginRequestRepository;
use App\Repository\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * Modification de la pré-admission.
     *
     * @Route("/support/{id}/originRequest", name="support_originRequest", methods="GET|POST")
     *
     * @param int $id //SupportGroup
     */
    public function editOriginRequest(int $id, Request $request): Response
    {
        $supportGroup = $this->repoSupportGroup->findSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $originRequest = $this->repo->findOriginRequest($supportGroup);

        if (!$originRequest) {
            $originRequest = new OriginRequest();
            $originRequest->setSupportGroup($supportGroup);
        }

        $form = ($this->createForm(OriginRequestType::class, $originRequest))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateOriginRequest($originRequest);
        }

        return $this->render('app/originRequest/originRequest.html.twig', [
            'support' => $supportGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Met à jour l'évaluation sociale du groupe.
     */
    protected function updateOriginRequest(OriginRequest $originRequest)
    {
        $originRequest->getSupportGroup()
                        ->setUpdatedAt(new \DateTime())
                        ->setUpdatedBy($this->getUser());

        $this->manager->persist($originRequest);
        $this->manager->flush();

        $this->addFlash('success', 'Les informations ont été mises à jour.');
    }
}
