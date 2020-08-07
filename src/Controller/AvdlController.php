<?php

namespace App\Controller;

use App\Entity\Avdl;
use App\Entity\SupportGroup;
use App\Repository\AvdlRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SupportGroupRepository;
use App\Controller\Traits\ErrorMessageTrait;
use App\Form\Support\SupportGroupAvdlType;
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
        $supportGroup = $this->repoSupportGroup->findSupportAvdlById($id);
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        // Si le service n'est pas AVDL, redirige vers la page d'accueil du suivi
        if ($supportGroup->getService()->getId() != 5) {
            return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
        }

        $form = ($this->createForm(SupportGroupAvdlType::class, $supportGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateAvdl($supportGroup);
        }

        $formCoeff = ($this->createForm(SupportCoefficientType::class, $supportGroup))
            ->handleRequest($request);

        if ($this->isGranted('ROLE_ADMIN') && $formCoeff->isSubmitted() && $formCoeff->isValid()) {
            $this->manager->flush();

            $this->addFlash('success', 'Le coefficient du suivi est mis à jour.');

            return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
        }

        return $this->render('app/support/supportGroupEdit.html.twig', [
            'support' => $supportGroup,
            'form' => $form->createView(),
            'formCoeff' => $formCoeff->createView(),
        ]);
    }

    /**
     * Met à jour l'AVDL.
     */
    protected function updateAvdl(SupportGroup $supportGroup)
    {
        $avdl = $supportGroup->getAvdl();

        $supportGroup
            ->setStatus($this->getStatus($avdl))
            ->setStartDate($this->getStartDate($avdl))
            ->setEndDate($this->getEndDate($avdl))
            ->setCoefficient($this->getCoeffSupport($avdl))
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $this->manager->flush();

        $this->addFlash('success', 'Les modifications sont enregistrées.');
    }

    /**
     * Donne le statut du suivi.
     */
    protected function getStatus(Avdl $avdl): int
    {
        if ($avdl->getSupportEndDate() || ($avdl->getDiagEndDate() && $avdl->getSupportStartDate() == null)) {
            return 4; // Terminé
        }

        return 2; // En cours
    }

    /**
     * Donne la date de début du suivi.
     */
    protected function getStartDate(Avdl $avdl): ?\DateTimeInterface
    {
        return min([
            $avdl->getDiagStartDate(),
            $avdl->getSupportStartDate(),
        ]);
    }

    /**
     * Donne la date de fin du suivi.
     */
    protected function getEndDate(Avdl $avdl): ?\DateTimeInterface
    {
        return max([
            $avdl->getDiagEndDate(),
            $avdl->getSupportEndDate(),
        ]);
    }

    /**
     * Donne le coefficient du suivi.
     *
     * @return float
     */
    protected function getCoeffSupport(Avdl $avdl): int
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
