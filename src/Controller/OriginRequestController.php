<?php

namespace App\Controller;

use App\Entity\SupportGroup;
use App\Entity\OriginRequest;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Evaluation\OriginRequestType;
use App\Repository\SupportGroupRepository;
use App\Repository\OriginRequestRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OriginRequestController extends AbstractController
{
    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, SupportGroupRepository $repoSupportGroup, OriginRequestRepository $repo)
    {
        $this->manager = $manager;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repo = $repo;
    }

    /**
     * Modification d'une évaluation sociale
     * 
     * @Route("/support/{id}/originRequest", name="support_originRequest", methods="GET|POST")
     * @param SupportGroup $supportGroup
     * @param Request $request
     * @return Response
     */

    public function editOriginRequest($id, Request $request): Response
    {
        $supportGroup = $this->repoSupportGroup->findSupportById($id);
        $this->denyAccessUnlessGranted("VIEW", $supportGroup);

        $originRequestGroup = $this->repo->findOneBy(["supportGroup" => $supportGroup]);

        $form = $this->createForm(OriginRequestType::class, $originRequestGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateOriginRequest($originRequestGroup);
        }

        return $this->render("app/originRequest/originRequest.html.twig", [
            "support" => $supportGroup,
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Crée l'évaluation sociale du groupe
     *
     * @param SupportGroup $supportGroup
     */
    protected function createOriginRequest(SupportGroup $supportGroup)
    {
        $originRequestGroup = new OriginRequest();
        $now = new \DateTime();

        $originRequestGroup->setSupportGroup($supportGroup)
            ->setDate($now)
            ->setCreatedAt($now);

        $this->manager->persist($originRequestGroup);

        $this->createOriginRequestPeople($supportGroup, $originRequestGroup);

        $this->manager->flush();

        return $this->redirectToRoute("support_originRequest", ["id" => $supportGroup->getId()]);
    }

    /**
     * Crée l'évaluation sociale de toutes les personnes du groupe
     *
     * @param SupportGroup $supportGroup
     * @param OriginRequest $originRequestGroup
     */
    public function createOriginRequestPeople(SupportGroup $supportGroup, OriginRequest $originRequestGroup)
    {
        foreach ($supportGroup->getSupportPerson() as $supportPerson) {

            $originRequestPerson = new OriginRequestPerson();

            $originRequestPerson->setOriginRequest($originRequestGroup)
                ->setSupportPerson($supportPerson);

            $this->manager->persist($originRequestPerson);
        };
    }

    /**
     * Met à jour l'évaluation sociale du groupe
     * 
     * @param SupportGroup $supportGroup
     * @param OriginRequest $originRequestGroup
     */
    protected function updateOriginRequest(OriginRequest $originRequestGroup)
    {
        $originRequestGroup->getSupportGroup()->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $this->updateBudgetGroup($originRequestGroup);

        $this->manager->flush();

        $this->addFlash("success", "L'évaluation sociale a été mis à jour.");
    }
}
