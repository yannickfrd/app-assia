<?php

namespace App\Controller\Event;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Event\Task;
use App\Entity\Support\SupportGroup;
use App\Form\Event\TaskSearchType;
use App\Form\Event\TaskType;
use App\Form\Model\Event\TaskSearch;
use App\Form\Support\Event\SupportTaskSearchType;
use App\Repository\Event\TaskRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Service\Event\TaskManager;
use App\Service\Event\TaskPaginator;
use App\Service\Export\TaskExport;
use App\Service\SupportGroup\SupportManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaskController extends AbstractController
{
    use ErrorMessageTrait;

    /**
     * @Route("/tasks", name="task_index", methods="GET|POST")
     */
    public function index(Request $request, TaskPaginator $paginator): Response
    {
        $form = $this->createForm(TaskSearchType::class, $search = new TaskSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search, $paginator->getTaskRepository());
        }

        $formTask = $this->createForm(TaskType::class, new Task())
            ->handleRequest($request);

        return $this->render('app/event/task/task_index.html.twig', [
            'form' => $form->createView(),
            'form_task' => $formTask->createView(),
            'tasks' => $paginator->paginate($request, $search),
        ]);
    }

    /**
     * @Route("/support/{id}/tasks", name="support_task_index", methods="GET")
     */
    public function supportTasksIndex(int $id, SupportManager $supportManager,
        Request $request, TaskPaginator $paginator): Response
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $formSearch = $this->createForm(SupportTaskSearchType::class, $search = new TaskSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search, $paginator->getTaskRepository(), $supportGroup);
        }

        $formTask = $this->createForm(TaskType::class, new Task(), [
            'support_group' => $supportGroup,
        ]);

        return $this->render('app/event/task/support_task_index.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'form_task' => $formTask->createView(),
            'tasks' => $paginator->paginate($request, $search, $supportGroup),
        ]);
    }

    /**
     * @Route("/task/new", name="task_new", methods="POST")
     * @Route("/support/{id}/task/new", name="support_task_new", methods="POST")
     */
    public function new(?int $id = null, Request $request, EntityManagerInterface $em,
        TranslatorInterface $translator): JsonResponse
    {
        $task = new Task();

        // If new support task
        if ($id) {
            /** @var SupportGroupRepository $supportGroupRepo */
            $supportGroupRepo = $em->getRepository(SupportGroup::class);
            $supportGroup = $supportGroupRepo->findSupportById($id);

            $this->denyAccessUnlessGranted('EDIT', $supportGroup);

            $task->setSupportGroup($supportGroup);
        }

        $form = $this->createForm(TaskType::class, $task)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setStart(new \DateTime());

            $em->persist($task);
            $em->flush();

            TaskManager::deleteCacheItems($task);

            return $this->json([
                'action' => 'create',
                'alert' => 'success',
                'msg' => $translator->trans('task.created_successfully', ['%task_title%' => $task->getTitle()], 'app'),
                'task' => $task,
            ], 200, [], ['groups' => Task::SERIALIZER_GROUPS]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Donne un objet pour les requêtes ajax.
     *
     * @Route("/task/{id}/show", name="task_show", methods="GET")
     */
    public function show(int $id, TaskRepository $taskRepo, NormalizerInterface $normalizer): JsonResponse
    {
        $task = $taskRepo->findTask($id);

        $this->denyAccessUnlessGranted('VIEW', $task);

        return $this->json([
            'action' => 'show',
            'task' => $normalizer->normalize($task, 'json', ['groups' => Task::SERIALIZER_GROUPS]),
        ]);
    }

    /**
     * @Route("/task/{id}/edit", name="task_edit", methods="POST")
     * @IsGranted("EDIT", subject="task")
     */
    public function edit(Task $task, Request $request, EntityManagerInterface $em,
        TranslatorInterface $translator): JsonResponse
    {
        $form = $this->createForm(TaskType::class, $task)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            TaskManager::deleteCacheItems($task);
            
            return $this->json([
                'action' => 'edit',
                'alert' => 'success',
                'msg' => $translator->trans('task.updated_successfully', ['%task_title%' => $task->getTitle()], 'app'),
                'task' => $task,
            ], 200, [], ['groups' => Task::SERIALIZER_GROUPS]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * @Route("/task/{id}/delete", name="task_delete", methods="GET")
     * @IsGranted("DELETE", subject="task")
     */
    public function delete(Task $task, EntityManagerInterface $em, TranslatorInterface $translator): JsonResponse
    {
        $taskId = $task->getId();

        $em->remove($task);
        $em->flush();

        TaskManager::deleteCacheItems($task);

        return $this->json([
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => $translator->trans('task.deleted_successfully', ['%task_title%' => $task->getTitle()], 'app'),
            'task' => ['id' => $taskId],
        ]);
    }

    /**
     *@Route("/task/{id}/toggle-status", name="task_toggle_status")
     *@IsGranted("EDIT", subject="task")
     */
    public function toggleStatus(Task $task, EntityManagerInterface $em,
        TranslatorInterface $translator): JsonResponse
    {
        $task->toggleStatus();

        $em->flush();

        TaskManager::deleteCacheItems($task);

        return $this->json([
            'action' => 'toggle_status',
            'alert' => 'success',
            'msg' => $translator->trans('task.toogle_status', [
                '%task_title%' => $task->getTitle(),
                '%task_status%' => mb_strtolower($task->getStatusToString()),
            ], 'app'),
            'task' => $task,
            ], 200, [], ['groups' => Task::SERIALIZER_GROUPS]);
    }

    private function exportData(TaskSearch $search, TaskRepository $taskRepo, ?SupportGroup $supportGroup = null): Response
    {
        if ($supportGroup) {
            $search->setSupportGroup($supportGroup);
        }

        $tasks = $taskRepo->findTasksToExport($search);

        if (!$tasks) {
            $this->addFlash('warning', 'no_result_to_export');

            return $this->redirectToRoute('task_index', [], Response::HTTP_SEE_OTHER);
        }

        return (new TaskExport())->exportData($tasks);
    }
}
