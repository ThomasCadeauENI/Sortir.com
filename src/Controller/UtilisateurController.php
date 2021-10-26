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
use App\Service\FileUploader;
use App\Form\FileUploadType;



class UtilisateurController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('utilisateur/mon_profil.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }

    /**
     * @Route("/test_upload", name="test_upload")
     */
    public function excelCommunesAction(Request $request, FileUploader $file_uploader)
    {
        $form = $this->createForm(FileUploadType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $file = $form['upload_file']->getData();
            if ($file)
            {
                $file_name = $file_uploader->upload($file);
                if (null !== $file_name) // for example
                {
                    $directory = $file_uploader->getTargetDirectory();
                    $full_path = $directory.'/'.$file_name;
                    // Do what you want with the full path file...
                    // Why not read the content or parse it !!!
                }
                else
                {
                    // Oups, an error occured !!!
                }
            }
        }
        return $this->render('utilisateur/ajout_utilisateurs.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ajout_manuel_utilisateur", name="ajout_manuel_utilisateur")
     */
    public function ajoutManuelUtilisateur(Request $request, FileUploader $file_uploader)
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);


        if ($form->isSubmitted())
        {
            $role = [$request->request->get('utilisateur')['roles']];
            $utilisateur->setRoles($role);

            $pwd = ($request->get('utilisateur'))['password'];
            $utilisateur->setPassword(password_hash($pwd, PASSWORD_DEFAULT));

            $em = $this->getDoctrine()->getManager();
            $em->persist($utilisateur);
            $em->flush();
        }
        return $this->render('utilisateur/ajout_utilisateur_manuel.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/gestion_utilisateur", name="gestion_utilisateurs")
     */
    public function GestionUtilisateur(): Response
    {
        return $this->render('utilisateur/index.html.twig', [
        ]);
    }

    /**
     * @Route("/afficher_DtTableUtilisateurs", name="afficher_DtTableUtilisateurs")
     */
    public function DtTableUtilisateurs():Response
    {
        $em=$this->getDoctrine();
        $repo = $em->getRepository(Utilisateur::class);
        $users = $repo->findAll();
        return $this->render('partialView/DtTableUtilisateurs.html.twig', [
            'users' => $users
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

        if ($form->isSubmitted() ) {
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

                $name = $_FILES[$form->getName('photo')]['name'];
                $tmp = $_FILES[$form->getName('photo')]['tmp_name'];

                $nom = $name['photo'];
                move_uploaded_file($tmp['photo'], $nom);
                $utilisateur->setPhoto($nom);


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
