<?php

namespace App\Controller;

use App\Form\Import\ImportType;
use App\Form\Model\Import;
use App\Service\Import\ImportDatasAMH;
use App\Service\Import\ImportDatasHebergement;
use App\Service\Import\ImportDatasHotel;
use App\Service\Import\ImportDatasOC;
use App\Service\Import\ImportDatasPAF;
use App\Service\Import\ImportDatasRdv;
use App\Service\Import\ImportDatasUser;
use App\Service\Import\UpdateDatasAMH;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    protected $import;

    public function __construct(Import $import)
    {
        $this->import = $import;
    }

    /**
     * Import de données du pôle Hébergement.
     *
     * @Route("import_heb", name="import_heb", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function importHebergement(Request $request, ImportDatasHebergement $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_heb.csv');
    }

    /**
     * Import de données de l'opération ciblée hôtel.
     *
     * @Route("import_oc", name="import_oc", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function importOC(Request $request, ImportDatasOC $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_oc.csv');
    }

    /**
     * Import de données de l'AMH.
     *
     * @Route("import_amh", name="import_amh", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function importAMH(Request $request, ImportDatasAMH $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_amh.csv');
    }

    /**
     * Mise à jour des données de l'AMH.
     *
     * @Route("update_import_amh", name="update_import_amh", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function updateAMH(Request $request, UpdateDatasAMH $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_amh.csv');
    }

    /**
     * Import des hôtels.
     *
     * @Route("import_hotels", name="import_hotels", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function importHotel(Request $request, ImportDatasHotel $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_hotels.csv');
    }

    /**
     * Import des utilisateurs.
     *
     * @Route("import_users", name="import_users", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function importUser(Request $request, ImportDatasUser $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_users.csv');
    }

    /**
     * Import des RDVs.
     *
     * @Route("import_rdvs", name="import_rdvs", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function importRdv(Request $request, ImportDatasRdv $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_rdvs.csv');
    }

    /**
     * Import des PAFs.
     *
     * @Route("import_paf", name="import_paf", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function importPAF(Request $request, ImportDatasPAF $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_paf.csv');
    }

    /**
     * Importe les données.
     */
    protected function import(Request $request, object $importDatas, string $filename): Response
    {
        $file = \dirname(__DIR__).'/../public/import/datas/'.$filename;

        $datas = $importDatas->getDatas($file);

        $form = ($this->createForm(ImportType::class, $this->import))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nbItems = $importDatas->importInDatabase($file, $this->import->getService());
            if ($nbItems > 0) {
                $this->addFlash('success', $nbItems.' entrées ont été importées !');

                return $this->redirectToRoute('home');
            } else {
                $this->addFlash('warning', 'Aucune entrée n\'a été importée.');
            }
        } else {
            // dump($datas);
        }

        return $this->render('app/import.html.twig', [
            'form' => $form->createView(),
            'datas' => $datas,
        ]);
    }
}
