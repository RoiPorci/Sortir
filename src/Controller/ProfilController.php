<?php

namespace App\Controller;


use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    /**
     * @Route("/profil", name="profil_updatedProfil")
     */
    public function updatedProfil(EntityManagerInterface $manager, Request $request): Response {

        //Récupérer les informations de l'utilisateur connecté
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setDateUpdated(new \DateTime());

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Profil modifié ! ');

            return $this->redirectToRoute('profil_updatedProfil');
        }

        return $this->render('profil/updateProfil.html.twig', [
            'profilForm' => $form->createView()
        ]);
    }
}
