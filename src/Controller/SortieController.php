<?php

namespace App\Controller;

use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\SortieType;

#[Route('/sortie', 'sortie_')]
class SortieController extends AbstractController
{
    /**
     * @Route ("/", name="homepage")
     */
    public function index(Request $request): Response {
        $sortie = new Sortie();

        $form = $this->createForm(SortieType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            dump($sortie);die;
        }
        return $this->render('sortie/index.html.twig');
    }

    #[Route('/creer', 'creer')]
    public function creer_sortie(Request $request): Response
    {
        $sortie = new Sortie();

        $form = $this->createForm(SortieType::class, $sortie);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $sortie->setEtat('NC');

            $em = $this->getDoctrine()->getManager();
            $em->persist($sortie);
            $em->flush();
        }

        return $this->render('sortie/creer_sortie.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/afficher/{id}', 'afficher')]
    public function afficher(Request $request, $id): Response{

        $sortie = $this->getDoctrine()->getRepository(Sortie::class)->find($id);

        return $this->render('sortie/affiche.html.twig', [
            'sortie' => $sortie
        ]);
    }

    #[Route('/profil', 'profil')]
    public function profil(){
        return $this->render ('utilisateur/profil.html.twig');
    }
}
