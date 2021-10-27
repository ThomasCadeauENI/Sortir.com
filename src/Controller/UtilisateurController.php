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
    public function updateMonProfil(Request $request, UserPasswordEncoderInterface $userPasswordHasherInterface): Response
    {
        $utilisateur = $this->getUser();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        $entityManager = $this->getDoctrine();
        $repo = $entityManager->getRepository(Ville::class);
        $villes = $repo->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $confirmPassword = ($request->get('utilisateur'))["ConfirmPassword"];
            $password = ($request->get('utilisateur'))['password'];
            $pseudo = ($request->get('utilisateur'))['pseudo'];
            $prenom = ($request->get('utilisateur'))['prenom'];
            $nom = ($request->get('utilisateur'))['nom'];
            $num_tel = ($request->get('utilisateur'))['num_tel'];
            $email = ($request->get('utilisateur'))['email'];
            $id_ville = ($request->get('utilisateur'))["id_ville"];

            if ($password == $confirmPassword && $userPasswordHasherInterface->isPasswordValid($this->getUser(), $password)) {
                $ville = $repo->find($id_ville);
                $utilisateur->setIdVille($ville);

                $utilisateur->setPseudo($pseudo);
                $utilisateur->setPrenom($prenom);
                $utilisateur->setNom($nom);
                $utilisateur->setNumTel($num_tel);
                $utilisateur->setEmail($email);

                $tmp = $form->get('ImportPhoto')->getData();
                $newPath =  "../public/images/";
                $nomPhoto = $tmp->getClientOriginalName();
                $pathName = $tmp->getPath().'/'.$nomPhoto;

                $tmp->move(
                    $newPath,
                    $pathName
                );
                $utilisateur->setPhoto($nomPhoto);

                $em = $this->getDoctrine()->getManager();
                $em->persist($utilisateur);
                $em->flush();
                $this->addFlash('success', "L'utilisateur est modifié avec succès !");
            }else{
                $this->addFlash('danger', "Le mot de passe est incorrecte !");
            }
        }
        return $this->render('utilisateur/mon_profil.html.twig', [
            'form' => $form->createView(),
            'villes' => $villes,
        ]);
    }

    /**
     * @Route("/profil/{id}", name="profil")
     */
    public function monProfil(Utilisateur  $utilisateur): Response{
        return $this->render('utilisateur/profil.html.twig', [
            'user' => $utilisateur,
        ]);
    }
}
