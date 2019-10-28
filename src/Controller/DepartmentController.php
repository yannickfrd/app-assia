<?php

namespace App\Controller;

use App\Entity\Department;
use App\Form\DepartmentType;
use App\Entity\DepartmentSearch;
use App\Form\DepartmentSearchType;
use App\Repository\DepartmentRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DepartmentController extends AbstractController
{
    private $manager;
    private $repo;
    private $request;
    private $security;

    public function __construct(ObjectManager $manager, DepartmentRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
    }

    /**
     * Permet de rechercher un service
     * 
     * @Route("/list/departments", name="list_departments")
     * @return Response
     */
    public function listDepartment(Request $request, DepartmentSearch $departmentSearch = null, PaginatorInterface $paginator): Response
    {
        $departmentSearch = new DepartmentSearch();

        $form = $this->createForm(DepartmentSearchType::class, $departmentSearch);
        $form->handleRequest($request);

        $search = $request->query->get("search");


        if ($request->query->all()) {
            $departments =  $paginator->paginate(
                $this->repo->findAllDepartmentsQuery($departmentSearch, $search),
                $request->query->getInt("page", 1), // page number
                20 // limit per page
            );
            $departments->setPageRange(5);
            $departments->setCustomParameters([
                "align" => "right", // alignement de la pagination
            ]);
        }

        return $this->render("app/listdepartments.html.twig", [
            "departments" => $departments ?? null,
            "departmentSearch" => $departmentSearch,
            "form" => $form->createView(),
            "current_menu" => "list_departments"
        ]);
    }

    /**
     * Voir la fiche individuelle
     * 
     * @Route("/department/{id}", name="department_show", methods="GET|POST")
     *  @return Response
     */
    public function departmentShow(Department $department, Request $request): Response
    {
        $form = $this->createForm(DepartmentType::class, $department);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $department->setUpdatedAt(new \DateTime())
            //     ->setUpdatedBy($this->security->getdepartment());

            $this->manager->flush();

            $this->addFlash("success", "Les modifications ont été enregistrées.");
        }

        return $this->render("app/department.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }
}
