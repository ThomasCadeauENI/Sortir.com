<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    /**
     * @Route("login/forgot_password", name="forgot_password")
     */
    public function forgotPassword(Request $request): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user = $this->getDoctrine()->getRepository(Utilisateur::class)->findOneBy(["email"=>$form->getViewData()["email"]]);
            if($user)
            {
                return $this->redirectToRoute('reset_password', ['id' => $user->getId()]);
            }else{
                $this->addFlash("danger", "Aucun utilisateur avec ce mail!");
            }
        }
        return $this->render('security/forgot_password.html.twig',
        [
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route("login/reset_password/{id}", name="reset_password")
     */
    public function resetPassword(Utilisateur $user, Request $request,UserPasswordEncoderInterface $userPasswordHasherInterface): Response
    {
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        dump(21);
        if($form->isSubmitted() && $form->isValid()) {
            dump($form->getViewData() != null);
            dump($form->getViewData()["mdp"] != null);
            dump($form->getViewData()["mdp2"] != null);
            dump($form->getViewData()["mdp"] == $form->getViewData()["mdp2"]);
            dump($userPasswordHasherInterface->isPasswordValid($user, $form->getViewData()["mdp"]));
            dump($user);
            if($form->getViewData() != null && $form->getViewData()["mdp"] != null && $form->getViewData()["mdp2"] != null && $form->getViewData()["mdp"] == $form->getViewData()["mdp2"]){
                $user->setPassword(password_hash($form->getViewData()["mdp"], PASSWORD_BCRYPT));
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $this->addFlash("success","Mot de passe changé !");
                return $this->redirectToRoute('homepage');
            }
        }
        if($form->getViewData() != null && $userPasswordHasherInterface->isPasswordValid($user, $form->getViewData()["mdp"]) && $form->getViewData()["mdp"] != "" && $form->getViewData()["mdp"] != null){
            $this->addFlash("danger", "Mot de passe éclaté !!");
        }
        return $this->render('security/reset_password.html.twig',
        [
            'form'=>$form->createView()
        ]);


    }
}
