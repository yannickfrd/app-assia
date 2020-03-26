<?php

namespace App\Controller;

use App\Repository\RdvRepository;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use App\Repository\PersonRepository;
use App\Repository\DocumentRepository;
use App\Repository\GroupPeopleRepository;
use App\Repository\SupportGroupRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController
{
    protected $repoUser;
    protected $repoPerson;
    protected $repoGroupPeople;
    protected $repoSupport;
    protected $repoNote;
    protected $repoRdv;
    protected $repoDocument;

    public function __construct(PersonRepository $repoPerson, GroupPeopleRepository $repoGroupPeople, UserRepository $repoUser, SupportGroupRepository $repoSupport, NoteRepository $repoNote, RdvRepository $repoRdv, DocumentRepository $repoDocument)
    {
        $this->repoUser = $repoUser;
        $this->repoPerson = $repoPerson;
        $this->repoGroupPeople = $repoGroupPeople;
        $this->repoSupport = $repoSupport;
        $this->repoNote = $repoNote;
        $this->repoRdv = $repoRdv;
        $this->repoDocument = $repoDocument;
    }

    /**
     * @Route("/home", name="home")
     * @Route("/")
     * @return Response
     */
    public function home(): Response
    {
        $cache = new FilesystemAdapter();

        if ($this->getUser()->getStatus() == 1) {
            return $this->dashboardSocialWorker($cache);
        }
        if ($this->isGranted("ROLE_SUPER_ADMIN")) {
            return $this->dashboardAdmin($cache);
        }
        return $this->dashboardSocialWorker($cache);
    }

    protected function dashboardSocialWorker($cache)
    {
        $userSupports = $cache->getItem("stats.user" . $this->getUser()->getId() . "_supports");

        if (!$userSupports->isHit()) {
            $userSupports->set($this->repoSupport->findAllSupportsFromUser($this->getUser()));
            $userSupports->expiresAfter(2 * 60);  // 5 * 60 seconds
            $cache->save($userSupports);
        }

        return $this->render("app/home/home.html.twig", [
            "supports" => $userSupports->get(),
            "notes" => $this->repoNote->findAllNotesFromUser($this->getUser(), 10),
            "rdvs" => $this->repoRdv->findAllRdvsFromUser($this->getUser(), 10)
        ]);
    }

    protected function dashboardAdmin($cache)
    {
        $indicators = $cache->getItem("stats.indicators");

        if (!$indicators->isHit()) {

            $datas = [];
            $datas["Nombre de personnes"] = (int) $this->repoPerson->findAllPeople();
            $datas["Nombre de groupes"] = (int) $this->repoGroupPeople->countAllGroups();
            $datas["Nombre de suivis"] = (int) $this->repoSupport->countAllSupports();
            $datas["Nombre de suivis en cours"] = (int) $this->repoSupport->countAllSupports(["status" => 2]);
            $datas["Nombre de notes"] =  (int) $this->repoNote->countAllNotes();
            $datas["Nombre de RDVs"] = (int) $this->repoRdv->countAllRdvs();
            $datas["Nombre de documents"] = $this->repoDocument->countAllDocuments() . " (" . round($this->repoDocument->SumSizeAllDocuments() / 1024 / 1024) . " Mo.)";

            $indicators->set($datas);
            $indicators->expiresAfter(5 * 60);  // 5 * 60 seconds
            $cache->save($indicators);
        }


        $usersIndicators = $cache->getItem("stats.users_indicators");

        if (!$usersIndicators->isHit()) {

            $users = [];

            /** @param User $user */
            foreach ($this->repoUser->findUsers(["status" => 1]) as $user) {
                $users[] = [
                    "id" => $user->getId(),
                    "name" => $user->getFullname(),
                    "supports" => (int) $this->repoSupport->countAllSupports(["user" => $user]),
                    "activeSupports" =>  (int) $this->repoSupport->countAllSupports([
                        "user" => $user,
                        "status" => 2
                    ]),
                    "notes" => (int) $this->repoNote->countAllNotes(["user" => $user]),
                    "rdvs" => (int) $this->repoRdv->countAllRdvs(["user" => $user]),
                ];
            }
            $usersIndicators->set($users);
            $usersIndicators->expiresAfter(5 * 60);  // 5 * 60 seconds
            $cache->save($usersIndicators);
        }

        return $this->render("app/home/dashboard.html.twig", [
            "datas" => $indicators->get(),
            "users" => $usersIndicators->get()
        ]);
    }
}
