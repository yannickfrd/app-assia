<?php

namespace App\Controller\Support;

use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Form\Support\Support\AddPersonToSupportType;
use App\Service\Grammar;
use App\Service\SupportGroup\SupportManager;
use App\Service\SupportGroup\SupportPeopleAdder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SupportPersonController extends AbstractController
{
    /**
     * Ajout de nouvelles personnes au suivi.
     *
     * @Route("/support/{id}/add_person", name="support_add_person", methods="POST")
     */
    public function addPersonToSupport(
        SupportGroup $supportGroup,
        Request $request,
        SupportPeopleAdder $supportPeopleAdder,
        SupportManager $supportManager
    ): Response {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = $this->createForm(AddPersonToSupportType::class, null, [
            'attr' => ['supportGroup' => $supportGroup],
        ])->handleRequest($request);

        $rolePerson = $form->get('rolePerson')->getData();

        if ($rolePerson && $supportPeopleAdder->addPersonToSupport($supportGroup, $rolePerson)) {
            $supportManager->update($supportGroup);
        }

        return $this->redirectToRoute('support_edit', ['id' => $supportGroup->getId()]);
    }

    /**
     * Supprime la personne du suivi social.
     *
     * @Route("/support-person/{id}/delete/{_token}", name="support_peron_delete", methods="GET")
     */
    public function delete(
        SupportPerson $supportPerson,
        string $_token,
        SupportManager $supportManager,
        EntityManagerInterface $em
    ): Response {
        $error = false;

        $supportGroup = $supportPerson->getSupportGroup();

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        // Vérifie si le token est valide avant de retirer la personne du suivi social
        if (!$this->isCsrfTokenValid('remove'.$supportPerson->getId(), $_token)) {
            $error = true;
            $this->addFlash('danger', 'Une erreur s\'est produite (Token invalide).');
        }
        // Vérifie si la personne est le demandeur principal
        if ($supportPerson->getHead()) {
            $error = true;
            $this->addFlash('danger', 'Le demandeur principal ne peut pas être retiré du suivi.');
        }
        // Vérifie si le nombre de personne dans le suivi est supérieur à 1
        if ($supportGroup->getSupportPeople()->count() <= 1) {
            $error = true;
            $this->addFlash('danger', 'Le suivi doit être constitué d\'au moins une personne.');
        }

        if (false === $error) {
            try {
                $supportGroup->removeSupportPerson($supportPerson);

                $supportManager->update($supportGroup);

                $this->addFlash('warning', $supportPerson->getPerson()->getFullname().' est retiré'.
                    Grammar::gender($supportPerson->getPerson()->getGender()).' du suivi social.');
            } catch (\Exception $e) {
                throw $e;
                $this->addFlash('danger', 'Une erreur s\'est produite ('.$e->getMessage().').');
            }
        }

        return $this->redirectToRoute('support_edit', ['id' => $supportGroup->getId()]);
    }

    /**
     * Récupère la liste des personnes rattachées à un suivi.
     *
     * @Route("/support/{id}/people", name="support_people", methods="GET")
     */
    public function getPeopleInSupportGroup(int $id, SupportManager $supportManager, NormalizerInterface $normalizer): JsonResponse
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        return $this->json([
            'action' => 'getSupportPeople',
            'support_people' => $normalizer->normalize($supportGroup->getSupportPeople(), null, [
                'groups' => ['show_support_person', 'show_person'],
            ]),
        ]);
    }
}
