<?php

namespace App\Controller;

use App\Form\Import\ImportType;
use App\Form\Model\Import;
use App\Service\Import\ImportDatasAMH;
use App\Service\Import\ImportDatasHebergement;
use App\Service\Import\ImportDatasHotel;
use App\Service\Import\ImportDatasOC;
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
     * Importe les données.
     */
    protected function import(Request $request, object $importDatas, string $filename): Response
    {
        $file = \dirname(__DIR__).'/../public/import/datas/'.$filename;

        $datas = $importDatas->getDatas($file);

        $form = ($this->createForm(ImportType::class, $this->import))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $items = $importDatas->importInDatabase($file, $this->import->getService());
            $nbItems = count($items);
            if ($nbItems > 0) {
                $this->addFlash('success', $nbItems.' entrées ont été importées !');
            } else {
                $this->addFlash('warning', 'Aucune entrée n\'a été importée.');
            }
        } else {
            dump($datas);
        }

        return $this->render('app/import.html.twig', [
            'form' => $form->createView(),
            'datas' => $datas,
        ]);
    }
}
