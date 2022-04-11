<?php

declare(strict_types=1);

namespace App\Controller\Support;

use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Form\Support\Support\AddPersonToSupportType;
use App\Repository\Support\SupportGroupRepository;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Grammar;
use App\Service\SupportGroup\SupportManager;
use App\Service\SupportGroup\SupportPeopleAdder;
use App\Service\SupportGroup\SupportRestorer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SupportPersonController extends AbstractController
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
    public function delete(SupportPerson $supportPerson, string $_token, SupportManager $supportManager): Response
    {
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
     * @Route("/support-person/{id}/restore", name="support_person_restore", methods="GET")
     */
    public function restore(
        int $id,
        SupportPersonRepository $supportPersonRepo,
        SupportGroupRepository $supportGroupRepo,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        SupportRestorer $supportRestorer
    ): JsonResponse {
        $supportPerson = $supportPersonRepo->findSupportPerson($id, true);
        $support = $supportPerson->getSupportGroup();

        $this->denyAccessUnlessGranted('EDIT', $support);

        if (null !== $supportPerson->getSupportGroup()->getDeletedAt()) {
            $supportRestorer->restore($support);

            $msg = $translator->trans('support.restored_successfully', [
                    '%support_referent%' => $support->getReferent(),
                ], 'app');
        } else {
            $supportPerson->setDeletedAt(null);

            $msg = $translator->trans('support_person.restored_successfully', [
                '%support_name%' => $supportPerson->getPerson()->getFullname(),
            ], 'app');
        }

        $em->flush();

        SupportManager::deleteCacheItems($support);

        return $this->json([
            'action' => 'restore',
            'alert' => 'success',
            'msg' => $msg,
            'support' => ['id' => $support->getId()]
        ]);
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
            'action' => 'get_support_people',
            'support_people' => $normalizer->normalize($supportGroup->getSupportPeople(), null, [
                'groups' => ['show_support_person', 'show_person'],
            ]),
        ]);
    }
}
