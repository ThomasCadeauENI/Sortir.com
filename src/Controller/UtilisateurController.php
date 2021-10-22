<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Ville;
use App\Form\UtilisateurType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UtilisateurController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('utilisateur/mon_profil.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }

    /**
     * @Route("/utilisateur", name="mon_profil")
     */
    public function updateMonProfil(Request $request, UserPasswordEncoderInterface $userPasswordEncoderInterface): Response
    {
        $utilisateur = $this->getUser();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        $entityManager = $this->getDoctrine();
        $repo = $entityManager->getRepository(Ville::class);
        $villes = $repo->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $id = $utilisateur->getId();

            $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($id);

            $confirmPassword = $request->get('ConfirmPassword');
            $password = $request->get('password');
            $pseudo = $request->get('pseudo');
            $prenom = $request->get('prenom');
            $nom = $request->get('nom');
            $num_tel = $request->get('num_tel');
            $email = $request->get('email');
            $id_ville = $request->get('id_ville');

            if(!$utilisateur){
                throw $this->createNotFoundException(
                    'No product found for id '.$id
                );
            }

            $passwordHashed = $userPasswordEncoderInterface->encodePassword(
                $utilisateur,
                $form->get('password')->getData()
            );

            if($password == $confirmPassword && $utilisateur->getPassword()==$passwordHashed){
                $ville = $repo->find($id_ville);
                $utilisateur -> setIdVille($ville);

                $utilisateur->setPseudo($pseudo);
                $utilisateur->setPrenom($prenom);
                $utilisateur->setNom($nom);
                $utilisateur->setNumTel($num_tel);
                $utilisateur->setEmail($email);
                $em->persist($utilisateur);
                $em->flush();
            }
        }
        return $this->render('utilisateur/mon_profil.html.twig', [
            'form' => $form->createView(),
            'villes' => $villes,
        ]);
    }
}
