<?php

namespace App\Controller;


use App\Form\UserType;
use claviska\SimpleImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\ByteString;

class ProfilController extends AbstractController
{
    /**
     * @Route("/profil", name="profil_updatedProfil")
     */
    public function updatedProfil(EntityManagerInterface $manager, Request $request, UserPasswordEncoderInterface $encoder): Response {

        //Récupérer les informations de l'utilisateur connecté
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setDateUpdated(new \DateTime());

            $password = $form->get('password')->getData();

            if($password){
                $user->setPassword($encoder->encodePassword($user, $password));
            }

             /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('picture')->getData();

            if ($uploadedFile) {
            $newFileName = ByteString::fromRandom(30) . "." . $uploadedFile->guessExtension();
            try {
                $uploadedFile->move($this->getParameter('upload_dir'), $newFileName);
            } catch (\Exception $e){
                dd($e->getMessage());
            }

            $simpleImage = new SimpleImage();
            $simpleImage->fromFile($this->getParameter('upload_dir') . "/$newFileName")
                ->thumbnail(330,460)
                ->desaturate()
                ->toFile($this->getParameter('upload_dir') . "/small/$newFileName");

            $user->setPictureFilename($newFileName);
            }

            $manager->persist($user);

            $manager->flush();

            $this->addFlash('success', 'Profil modifié ! ');

            return $this->redirectToRoute('profil_updatedProfil');
        }

        return $this->render('profil/updateProfil.html.twig', [
            'profilForm' => $form->createView(),
            'user' => $user
        ]);
    }
}
