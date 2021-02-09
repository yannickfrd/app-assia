<?php

namespace App\Controller\Support;

use App\Entity\Organization\Accommodation;
use App\Entity\Support\AccommodationGroup;
use App\Entity\Support\AccommodationPerson;
use App\Entity\Support\SupportGroup;
use App\EntityManager\SupportManager;
use App\Form\Organization\Accommodation\AccommodationGroupType;
use App\Repository\Support\AccommodationGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller des hébergements des groupes de personnes.
 */
class AccommodationGroupController extends AbstractController
{
    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, AccommodationGroupRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Liste des hébergements du suivi social.
     *
     * @Route("support/{id}/accommodations", name="support_accommodations", methods="GET")
     */
    public function supportAccommodationsGroup(int $id, SupportManager $supportManager): Response
    {
        $supportGroup = $supportManager->getFullSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        return $this->render('app/organization/accommodation/supportAccommodationsGroup.html.twig', [
            'support' => $supportGroup,
        ]);
    }

    /**
     * Nouvel hébergement.
     *
     * @Route("/support/{id}/accommodation/new", name="support_accommodation_new", methods="GET|POST")
     */
    public function newAccommodationGroup(SupportGroup $supportGroup, AccommodationGroup $accommodationGroup = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        // Vérifie si une prise en charge existe déjà pour le suivi
        if ($supportGroup->getAccommodationGroups()) {
            foreach ($supportGroup->getAccommodationGroups() as $accommodationGroup) {
                if (null === $accommodationGroup->getEndDate()) {
                    $this->addFlash('warning', 'Attention, une autre prise en charge est déjà en cours pour ce suivi.');
                }
            }
        }

        $accommodationGroup = (new AccommodationGroup())
            ->setSupportGroup($supportGroup)
            ->setStartDate($supportGroup->getStartDate())
            ->setEndDate($supportGroup->getEndDate());

        $form = ($this->createForm(AccommodationGroupType::class, $accommodationGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createAccommodationGroup($supportGroup, $accommodationGroup);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', "Une erreur s'est produite");
        }

        return $this->render('app/organization/accommodation/accommodationGroup.html.twig', [
            'support' => $supportGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un hébergement.
     *
     * @Route("/support/accommodation_group/{id}", name="support_accommodation_edit", methods="GET|POST")
     *
     * @param int $id // AccommodationGroup
     */
    public function editAccommodationGroup(int $id, SupportManager $supportManager, Request $request): Response
    {
        $accommodationGroup = $this->repo->findAccommodationGroupById($id);
        // $supportGroup = $repoSupport->findSupportById($accommodationGroup->getSupportGroup()->getId());
        $supportGroup = $supportManager->getSupportGroup($accommodationGroup->getSupportGroup()->getId());

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = ($this->createForm(AccommodationGroupType::class, $accommodationGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updateAccommodationGroup($supportGroup, $accommodationGroup);
        }

        return $this->render('app/organization/accommodation/accommodationGroup.html.twig', [
            'support' => $supportGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Ajout de personnes à la prise en charge.
     *
     * @Route("/support/group_people_accommodation/{id}/add_people", name="support_group_people_accommodation_add_people", methods="GET")
     */
    public function addPeopleInAccommodation(AccommodationGroup $accommodationGroup): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $accommodationGroup->getSupportGroup());

        $countAddPeople = $this->createAccommodationPeople($accommodationGroup);

        if ($countAddPeople >= 1) {
            $this->addFlash('success', $countAddPeople.' personne(s) sont ajoutée(s) à la prise en charge.');
            $this->discacheSupport($accommodationGroup->getSupportGroup());
        } else {
            $this->addFlash('warning', 'Aucune personne n\'a été ajoutée.');
        }

        return $this->redirectToRoute('support_accommodation_edit', [
            'id' => $accommodationGroup->getId(),
        ]);
    }

    /**
     * Supprime la prise en charge du groupe.
     *
     * @Route("support/group-people-accommodation/{id}/delete", name="support_group_people_accommodation_delete", methods="GET")
     */
    public function deleteAccommodationGroup(AccommodationGroup $accommodationGroup): Response
    {
        $supportGroup = $accommodationGroup->getSupportGroup();

        $this->denyAccessUnlessGranted('DELETE', $supportGroup);

        $this->manager->remove($accommodationGroup);
        $this->manager->flush();

        $this->discacheSupport($supportGroup);

        $this->addFlash('warning', 'La prise en charge est supprimée.');

        return $this->redirectToRoute('support_accommodations', ['id' => $supportGroup->getId()]);
    }

    /**
     * Supprime la prise en charge d'une personne.
     *
     * @Route("support/person-accommodation/{id}/delete", name="support_person_accommodation_delete", methods="GET")
     */
    public function deleteAccommodationPerson(AccommodationPerson $accommodationPerson): Response
    {
        $supportGroup = $accommodationPerson->getAccommodationGroup()->getSupportGroup();

        $this->denyAccessUnlessGranted('DELETE', $supportGroup);

        $this->manager->remove($accommodationPerson);
        $this->manager->flush();

        $this->discacheSupport($supportGroup);

        $this->addFlash('warning', $accommodationPerson->getPerson()->getFullname().' est retiré de la prise en charge.');

        return $this->redirectToRoute('support_accommodation_edit', [
            'id' => $accommodationPerson->getAccommodationGroup()->getId(),
        ]);
    }

    /**
     * Crée la prise en charge du groupe.
     */
    protected function createAccommodationGroup(SupportGroup $supportGroup, AccommodationGroup $accommodationGroup): Response
    {
        $accommodationGroup->setPeopleGroup($supportGroup->getPeopleGroup());

        $this->manager->persist($accommodationGroup);

        $this->createAccommodationPeople($accommodationGroup);

        $this->updateLocationSupportGroup($supportGroup, $accommodationGroup->getAccommodation());

        $this->manager->flush();

        $this->discacheSupport($supportGroup);

        $this->addFlash('success', "L'hébergement est créé.");

        return $this->redirectToRoute('support_accommodations', [
            'id' => $accommodationGroup->getSupportGroup()->getId(),
        ]);
    }

    /**
     * Met à jour l'adresse du suivi via l'adresse du groupe de places.
     */
    protected function updateLocationSupportGroup(SupportGroup $supportGroup, Accommodation $accommodation)
    {
        $supportGroup
            ->setAddress($accommodation->getAddress())
            ->setCity($accommodation->getCity())
            ->setZipcode($accommodation->getZipcode())
            ->setCommentLocation($accommodation->getCommentLocation())
            ->setLocationId($accommodation->getLocationId())
            ->setLat($accommodation->getLat())
            ->setLon($accommodation->getLon());
    }

    /**
     * Met à jour la prise en charge du groupe.
     */
    protected function updateAccommodationGroup(SupportGroup $supportGroup, AccommodationGroup $accommodationGroup)
    {
        foreach ($accommodationGroup->getAccommodationPeople() as $accommodationPerson) {
            $person = $accommodationPerson->getPerson();
            // if (null === $accommodationPerson->getEndDate()) {
            $accommodationPerson->setStartDate($accommodationGroup->getStartDate());
            // }
            if ($accommodationPerson->getStartDate() < $person->getBirthdate()) {
                $accommodationPerson->setStartDate($person->getBirthdate());
                $this->addFlash('warning', 'La date de début d\'hébergement ne peut pas être antérieure à la date de naissance de la personne ('.$person->getFullname().').');
            }
            if (null === $accommodationPerson->getEndDate()) {
                $accommodationPerson->setEndDate($accommodationGroup->getEndDate());
            }

            if (null === $accommodationPerson->getEndReason()) {
                $accommodationPerson->setEndReason($accommodationGroup->getEndReason());
            }
        }
        $this->manager->flush();

        $this->addFlash('success', 'L\'hébergement est mis à jour');

        $this->discacheSupport($supportGroup);

        return $this->redirectToRoute('support_accommodation_edit', ['id' => $accommodationGroup->getId()]);
    }

    /**
     * Crée les prises en charge individuelles.
     */
    protected function createAccommodationPeople(AccommodationGroup $accommodationGroup): int
    {
        $countAddPeople = 0;

        foreach ($accommodationGroup->getSupportGroup()->getSupportPeople() as $supportPerson) {
            // Vérifie si la personne n'est pas déjà rattachée à la prise en charge
            if (null === $supportPerson->getEndDate() && !in_array($supportPerson->getPerson()->getId(), $this->getPeopleInAccommodation($accommodationGroup))) {
                // Si elle n'est pas déjà pris en charge, on la créé
                $accommodationPerson = (new AccommodationPerson())
                    ->setAccommodationGroup($accommodationGroup)
                    ->setSupportPerson($supportPerson)
                    ->setPerson($supportPerson->getPerson())
                    ->setStartDate($accommodationGroup->getStartDate())
                    ->setEndDate($accommodationGroup->getEndDate());

                // Vérifie si la date de prise enn charge n'est pas antérieure à la date de naissance
                $person = $supportPerson->getPerson();
                if ($accommodationPerson->getStartDate() < $person->getBirthdate()) {
                    // Si c'est le cas, on prend en compte la date de naissance
                    $accommodationPerson->setStartDate($person->getBirthdate());
                }

                $this->manager->persist($accommodationPerson);
                ++$countAddPeople;
            }
        }
        $this->manager->flush();

        return $countAddPeople;
    }

    // Donne les ID des personnes rattachées à la prise en charge.
    protected function getPeopleInAccommodation(AccommodationGroup $accommodationGroup)
    {
        $people = [];
        foreach ($accommodationGroup->getAccommodationPeople() as $accommodationPerson) {
            $people[] = $accommodationPerson->getPerson()->getId();
        }

        return $people;
    }

    /**
     * Supprime l'item en cache du suivi social.
     */
    public function discacheSupport(SupportGroup $supportGroup): bool
    {
        return (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->deleteItem(SupportGroup::CACHE_FULLSUPPORT_KEY.$supportGroup->getId());
    }
}
