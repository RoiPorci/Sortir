<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ImportCsvType;
use App\Services\UserImporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/import/csv", name="admin_import_csv")
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function importUsersFromCsv(Request $request, UserImporter $importer): Response
    {

        $form = $this->createForm(ImportCsvType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            /** @var UploadedFile $uploadedCsv */
            $uploadedCsv = $form->get('fileCsv')->getData();
            $splFile = $uploadedCsv->openFile();

            while (!$splFile->eof()) {
                $data[] = $splFile->fgetcsv();
            }

            $importer->insertUsers($data);
        }

        return $this->render('admin/import-csv.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
