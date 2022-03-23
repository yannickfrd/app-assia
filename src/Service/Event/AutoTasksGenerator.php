<?php

namespace App\Service\Event;

use App\Entity\Admin\Setting;
use App\Entity\Evaluation\EvalAdmPerson;
use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvalHousingGroup;
use App\Entity\Evaluation\EvalProfPerson;
use App\Entity\Evaluation\EvalSocialPerson;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Event\Alert;
use App\Entity\Event\Task;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Repository\Event\TaskRepository;
use App\Repository\Organization\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

class AutoTasksGenerator
{
    private $em;
    private $userRepo;
    private $taskRepo;
    private $translator;
    private $cache;

    private int $nbTasks = 0;

    /** @var Setting */
    private $adminSetting;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepo, TaskRepository $taskRepo,
        TranslatorInterface $translator
    ) {
        $this->em = $em;
        $this->userRepo = $userRepo;
        $this->taskRepo = $taskRepo;
        $this->translator = $translator;
        $this->cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $this->adminSetting = $this->em->getRepository(Setting::class)->findOneBy([]) ?? new Setting();
    }

    public function generate(?SymfonyStyle $io = null): int
    {
        $users = $this->userRepo->findUsersWithActiveSupportsAndEval();

        if ($io) {
            $io->progressStart(count($users));
        }

        foreach ($users as $user) {
            $userSetting = $user->getSetting();

            if ($userSetting && false === $userSetting->getAutoEvaluationAlerts()) {
                continue;
            }

            $this->checkSupportsOfUser($user);

            $this->cache->deleteItems([
                User::CACHE_USER_TASKS_KEY.$user->getId(),
                User::CACHE_USER_SUPPORTS_KEY.$user->getId(),
            ]);

            if ($io) {
                $io->progressAdvance();
            }
        }

        if ($io) {
            $io->progressFinish();
        }

        $this->em->flush();

        return $this->nbTasks;
    }

    private function checkSupportsOfUser(User $user): void
    {
        foreach ($user->getReferentSupport() as $supportGroup) {
            /** @var EvaluationGroup */
            $evaluationGroup = $supportGroup->getEvaluationsGroup()->first();

            if (!$evaluationGroup) {
                continue;
            }

            foreach ($this->getEvalGroupProperties($evaluationGroup) as [$entity, $property, $title, $delay]) {
                $this->createTaskOrNot($entity, $property, $title, $delay, $user);
            }

            if (!$evaluationGroup->getEvaluationPeople()) {
                continue;
            }

            foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
                foreach ($this->getEvalPersonProperties($evaluationPerson) as [$entity, $property, $title, $delay]) {
                    $this->createTaskOrNot($entity, $property, $title, $delay, $user);
                }
            }

            $this->cache->deleteItems([
                SupportGroup::CACHE_SUPPORT_TASKS_KEY.$supportGroup->getId(),
                SupportGroup::CACHE_SUPPORT_NB_TASKS_KEY.$supportGroup->getId(),
            ]);
        }
    }

    private function getEvalGroupProperties(EvaluationGroup $evaluationGroup): array
    {
        return [
            [$evaluationGroup->getEvalHousingGroup(), 'siaoUpdatedRequestDate', 'update_siao_request',
                $this->adminSetting->getDelayToUpdateSiaoRequest(), ],
            [$evaluationGroup->getEvalHousingGroup(), 'socialHousingUpdatedRequestDate', 'update_social_housing_request',
                $this->adminSetting->getDelayToUpdateSocialHousingRequest(), ],
            [$evaluationGroup->getEvalHousingGroup(), 'endDomiciliationDate', 'update_domiciliation', null],
        ];
    }

    private function getEvalPersonProperties(EvaluationPerson $evaluationPerson): array
    {
        return [
            [$evaluationPerson->getEvalAdmPerson(), 'endValidPermitDate', 'update_paper', null],
            [$evaluationPerson->getEvalSocialPerson(), 'endRightsSocialSecurityDate', 'update_social_security_rights', null],
            [$evaluationPerson->getEvalProfPerson(), 'endRqthDate', 'update_rqth', null],
            [$evaluationPerson->getEvalBudgetPerson(), 'endRightsDate', 'update_resources_rights', null],
        ];
    }

    /**
     * @param EvalHousingGroup|EvalAdmPerson|EvalSocialPerson|EvalProfPerson|EvalBudgetPerson|null $evalEntity
     */
    private function createTaskOrNot(?object $evalEntity = null, string $property, string $title,
        int $delay = null, User $user): void
    {
        $method = 'get'.ucfirst($property);

        if (!$evalEntity || !$endDate = $this->getEndDate($evalEntity, $method, $delay)) {
            return;
        }

        $alertDate = $this->getAlertDate($endDate, $method, $user);

        if (method_exists($evalEntity, 'getEvaluationGroup')) {
            /** @var SupportGroup $supportGroup */
            $supportGroup = $evalEntity->getEvaluationGroup()->getSupportGroup();
        } else {
            /** @var SupportPerson $supportPerson */
            $supportPerson = $evalEntity->getEvaluationPerson()->getSupportPerson();
            $supportGroup = $supportPerson->getSupportGroup();
        }

        $autoTaskId = $property.'-'.$alertDate->format('Y-m-d')
            .(isset($supportPerson) ? $supportPerson->getId() : $supportGroup->getId());

        if (null === $alertDate || $alertDate > new \DateTime() || $this->taskExists($autoTaskId)) {
            return;
        }

        /** @var Task $task */
        $task = (new Task())
            ->setAutoTaskId($autoTaskId)
            ->setTitle($this->translator->trans($title, [], 'forms').' - '.(isset($supportPerson)
                ? $supportPerson->getPerson()->getFullname() : $supportGroup->getHeader()->getFullname()))
            ->addUser($user)
            ->setSupportGroup($supportGroup)
            ->setStart($alertDate)
            ->setEnd($endDate)
        ;

        $task->addAlert($this->createAlert($task));

        $this->em->persist($task);

        ++$this->nbTasks;
    }

    private function getEndDate(object $evalEntity, string $method, ?int $delay = null): ?\DateTime
    {
        /** @var \Datetime|null $endDate */
        $endDate = $evalEntity->$method();

        if (!$endDate || $endDate < (new \DateTime())->modify('-9 years')) {
            return null;
        }

        if ($delay) {
            $endDate->modify("+$delay months");
        }

        return $endDate->modify('+8 hours');
    }

    private function getAlertDate(\DateTime $endDate, string $method, User $user): ?\DateTime
    {
        $settingMethod = $method.'Delay';
        $delay = $user->getSetting() ? $user->getSetting()->$settingMethod() : $this->adminSetting->$settingMethod();

        return (clone $endDate)->modify("-$delay months");
    }

    private function taskExists(string $autoTaskId): bool
    {
        return 0 !== $this->taskRepo->count([
            'autoTaskId' => $autoTaskId,
            'status' => false,
        ]);
    }

    private function createAlert(Task $task): Alert
    {
        return (new Alert())
            ->setDate($task->getStart())
            ->setType(Alert::EMAIL_TYPE)
            ->setTask($task)
        ;
    }
}
