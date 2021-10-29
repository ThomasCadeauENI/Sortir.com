<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Ville;
use App\Form\UtilisateurType;
use SplFileObject;
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
                    $rowNo = 1;
                    // $fp is file pointer to file sample.csv
                    if (($fp = fopen($full_path, "r")) !== FALSE) {
                        while (($row = fgetcsv($fp, 1000, ";")) !== FALSE) {
                            $entityManager = $this->getDoctrine();
                            $repoV = $entityManager->getRepository(Ville::class);
                            $repoU = $entityManager->getRepository(Utilisateur::class);
                            if(count($row) == 3 && $row[0]!="nom"&&$row[1]!="prenom"&&$row[2]!="ville" ){
                                if($repoU->findBy(["nom"=>$row[0], "prenom"=>$row[1]]) == null){
                                    $user = new Utilisateur();
                                    $user->setNom($row[0]);
                                    $user->setPrenom($row[1]);
                                    $ville= $repoV->find($row[2]);
                                    $user->setIdVille($ville);
                                    $user->setActif(true);
                                    $user->setEmail($user->getPrenom().".".$user->getNom().date('Y')."@campus-zabi.fr");
                                    $user->setPseudo(substr($user->getPrenom(), 0, 1).$user->getNom());
                                    //user password 1er lettre prenom + 1er lettre nom + annee d'enregistrement
                                    $user->setPassword(password_hash(substr($user->getPrenom(), 0, 1).substr($user->getNom(), 0, 1).date('Y'), PASSWORD_BCRYPT));

                                    $em = $this->getDoctrine()->getManager();
                                    $em->persist($user);
                                    $em->flush();
                                    $rowNo++;
                                    $this->addFlash("success", "Utilisateur ".$user->getNom()." ".$user->getPrenom()." enregistré !");
                                }

                            }

                        }
                        fclose($fp);
                        unlink($full_path);
                        if($rowNo == 1){
                            $this->addFlash("danger", "Aucun utilisateur créé! ");
                        }
                        return $this->redirectToRoute('gestion_utilisateurs');
                    }
                }
                else
                {
                    $this->addFlash("danger", "Problème avc le fichier :/ ");

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
            if($this->getDoctrine()->getRepository(Utilisateur::class)->findBy(["pseudo"=>$utilisateur->getPseudo()]) == null && !$this->getDoctrine()->getRepository(Utilisateur::class)->findBy(["email"=>$utilisateur->getEmail()]) == null) {
                $role = [$request->request->get('utilisateur')['roles']];
                $utilisateur->setRoles($role);

                $pwd = ($request->get('utilisateur'))['password'];
                $utilisateur->setPassword(password_hash($pwd, PASSWORD_DEFAULT));

                $em = $this->getDoctrine()->getManager();
                $em->persist($utilisateur);
                $em->flush();
                return $this->redirectToRoute("gestion_utilisateurs");
            }else{
                $this->addFlash("danger", "Un utilisateur possède le même email et/ou pseudo!");
            }
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
     * @Route("/remove_user", name="remove_user")
     */
    public function Remove(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $user = $entityManager->getRepository(Utilisateur::class)->find($id);
        if($user){
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash("success", "Suppression de l'utilisateur ". $user->getPseudo(). "réussi !");
        }

        return $this->redirect('gestion_utilisateur');
    }
    /**
     * @Route("/toggleActif", name="toggleActif")
     */
    public function toggleActif(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $user = $entityManager->getRepository(Utilisateur::class)->find($id);
        if($user)
        {
            $user->setActif(!$user->getActif());
            $entityManager->flush();
        }
        return $this->redirect('gestion_utilisateur');

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

                $tmp = $form->get('ImportPhoto')->getData();
                if($tmp != null){
                    $newPath =  "../public/images/";
                    $nomPhoto = uniqid().$tmp->getClientOriginalName();
                    $pathName = $tmp->getPath().'/'.$nomPhoto;

                    $tmp->move(
                        $newPath,
                        $pathName
                    );
                    $utilisateur->setPhoto($nomPhoto);
                }

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
