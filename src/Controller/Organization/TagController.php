<?php

namespace App\Controller\Organization;

use App\Entity\Organization\Tag;
use App\Form\Model\Organization\TagSearch;
use App\Form\Organization\Tag\TagSearchType;
use App\Form\Organization\Tag\TagType;
use App\Repository\Organization\TagRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
    public const NB_ITEMS = 10;

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Affiche la liste des tags.
     *
     * @Route("/admin/tags", name="tags", methods="GET")
     * @IsGranted("ROLE_ADMIN")
     */
    public function listTags(Request $request, Pagination $pagination, TagRepository $tagRepo): Response
    {
        $form = $this->createForm(TagSearchType::class, $search = new TagSearch())
            ->handleRequest($request);

        return $this->render('app/organization/tag/list_tags.html.twig', [
            'form' => $form->createView(),
            'tags' => $pagination->paginate(
                $tagRepo->findTagsQuery($search),
                $request,
                self::NB_ITEMS
            ),
        ]);
    }

    /**
     * Nouveau tag.
     *
     * @Route("/admin/tag/new", name="admin_tag_new", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function newTag(Request $request): Response
    {
        $form = $this->createForm(TagType::class, $tag = new Tag())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($tag);
            $this->em->flush();

            $this->addFlash('success', 'L\'étiquette est créée.');

            return $this->redirectToRoute('tags');
        }

        return $this->render('app/organization/tag/tag.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un tag.
     *
     * @Route("/admin/tags/{id}/edit", name="admin_tag_edit", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function editTag(Tag $tag, Request $request): Response
    {
        $form = $this->createForm(TagType::class, $tag)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Les modifications sont enregistrées.');

            return $this->redirectToRoute('tags');
        }

        return $this->render('app/organization/tag/tag.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Suppression de tag.
     *
     * @Route("/admin/tags/{id}/delete", name="admin_tag_delete"), methods="GET|DELETE"
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function deleteTag(Tag $tag): RedirectResponse
    {
        $this->em->remove($tag);
        $this->em->flush();

        $this->addFlash('success', 'L\'étiquette est supprimée.');

        return $this->redirectToRoute('tags');
    }
}
