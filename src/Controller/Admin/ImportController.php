<?php

namespace App\Controller\Admin;

use App\Form\Admin\ImportType;
use App\Form\Model\Admin\Import;
use App\Service\Import\ImportDatasHebergement;
use App\Service\Import\ImportPAFDatas;
use App\Service\Import\ImportPlaceDatas;
use App\Service\Import\ImportUserDatas;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
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
     * @Route("/import_heb", name="import_heb", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function importHebergement(Request $request, ImportDatasHebergement $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_heb.csv');
    }

    /**
     * Import des hôtels.
     *
     * @Route("/import/places", name="import_places", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function importPlaces(Request $request, ImportPlaceDatas $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_places.csv');
    }

    /**
     * Import des utilisateurs.
     *
     * @Route("/import/users", name="import_users", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function importUser(Request $request, ImportUserDatas $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_users.csv');
    }

    /**
     * Import des PAFs.
     *
     * @Route("/import/paf", name="import_paf", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function importPAF(Request $request, ImportPAFDatas $importDatas): Response
    {
        return $this->import($request, $importDatas, 'import_paf.csv');
    }

    /**
     * Importe les données.
     */
    protected function import(Request $request, object $importDatas, string $filename): Response
    {
        $file = \dirname(__DIR__).'/../../public/import/datas/'.$filename;

        $datas = $importDatas->getDatas($file);

        $form = $this->createForm(ImportType::class, $this->import)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nbItems = count($importDatas->importInDatabase($file, $this->import->getServices(), $request));
            if ($nbItems > 0) {
                (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->clear();

                $this->addFlash('success', $nbItems.' entrées ont été importées !');

                return $this->redirectToRoute('home');
            } else {
                $this->addFlash('warning', 'Aucune entrée n\'a été importée.');
            }
            // } else {
            // dump($datas);
        }

        return $this->render('app/admin/import.html.twig', [
            'form' => $form->createView(),
            'datas' => $datas,
        ]);
    }
}
