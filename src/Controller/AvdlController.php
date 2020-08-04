<?php

namespace App\Controller;

use App\Entity\Avdl;
use App\Form\Avdl\AvdlType;
use App\Entity\SupportGroup;
use App\Repository\AvdlRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SupportGroupRepository;
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

    public function __construct(EntityManagerInterface $manager, SupportGroupRepository $repoSupportGroup, AvdlRepository $repo)
    {
        $this->manager = $manager;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repo = $repo;
    }

    /**
     * Modification d'un forumalaire AVDL.
     *
     * @Route("/support/{id}/avdl", name="support_avdl_edit", methods="GET|POST")
     */
    public function editAvdl(int $id, Request $request): Response
    {
        $supportGroup = $this->repoSupportGroup->findSupportById($id);
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        // Si le service n'est pas AVDL, redirige vers la page d'accueil du suivi
        if ($supportGroup->getService()->getId() != 5) {
            return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
        }

        $avdl = $this->repo->findOneBy(['supportGroup' => $supportGroup]) ?? new Avdl();

        $form = ($this->createForm(AvdlType::class, $avdl))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateAvdl($supportGroup, $avdl);
        }

        return $this->render('app/avdl/avdl.html.twig', [
            'support' => $supportGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Met à jour l'AVDL.
     */
    protected function updateAvdl(SupportGroup $supportGroup, Avdl $avdl)
    {
        if (null == $avdl->getId()) {
            $avdl->setSupportGroup($supportGroup);
            $this->manager->persist($avdl);
        }

        ($avdl->getSupportGroup())
            ->setCoefficient($this->getCoeffSupport($avdl))
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $this->manager->flush();

        $this->addFlash('success', 'Les modifications sont enregistrées.');
    }

    /**
     * Donne le coefficient du suivi.
     *
     * @return float
     */
    protected function getCoeffSupport(Avdl $avdl)
    {
        // Si accompagnement lourd : coeff 2
        if ($avdl->getSupportType() == 3) {
            return 2;
        }
        // Si prêt au logement : coeff 0.25
        if ($avdl->getReadyToHousing() == 1) {
            return 0.25;
        }
        // Sinon par défaut : coeff 1
        return 1;
    }
}
