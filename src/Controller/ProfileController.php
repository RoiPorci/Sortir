<?php

namespace App\Controller;


use App\Form\UserType;
use App\Repository\UserRepository;
use claviska\SimpleImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\ByteString;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profil/modifier", name="profil_updatedProfil" )
     */
    public function updatedProfil(EntityManagerInterface $manager, Request $request, UserPasswordEncoderInterface $encoder): Response {

        //Récupérer les informations de l'utilisateur connecté
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setDateUpdated(new \DateTime());

            //Modification du mot de passe si nécessaire
            $password = $form->get('password')->getData();

            if($password){
                $user->setPassword($encoder->encodePassword($user, $password));
            }

            //Modification de la photo de profil si nécessaire
             /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('picture')->getData();

            if ($uploadedFile) {
                // upload de la nouvelle image
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

                // suppression de l'originale
                unlink($this->getParameter('upload_dir') . "/$newFileName");

                // suppression de l'ancienne image si il y en a une
                $oldPicture = $user->getPictureFilename();
                if ($oldPicture){
                    unlink($this->getParameter('upload_dir') . "/small/$oldPicture");
                }

                // affectation de la nouvelle image à l'utilisateur
                $user->setPictureFilename($newFileName);
            }

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Profil modifié ! ');

            //Redirection
            return $this->redirectToRoute('profil_show', ['id' => $user->getId()]);
        }
        else {
            //Raffraîchissment de l'utilisateur connecté manuel pour éviter une déconnexion
            $manager->refresh($this->getUser());
        }

        return $this->render('profil/updateProfile.html.twig', [
            'profilForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profil/{id}", name="profil_show", requirements={"id"="\d+"})
     */
    public function showProfile(int $id, UserRepository $userRepository) {

        return $this->render('profil/showProfile.html.twig', [
            'user' => $userRepository->find($id)
        ]);
    }
}
