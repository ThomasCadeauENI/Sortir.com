<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\SortieType;

#[Route('/sortie', 'sortie_')]
class SortieController extends AbstractController
{
    #[Route('/', '')]
    public function index(): Response {
        return $this->render('sortie/index.html.twig');
    }

    #[Route('/creer', 'creer')]
    public function creer_sortie(Request $request): Response {
        $sortie = new Sortie();

        $form = $this->createForm(SortieType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            dump($sortie);die;
        }

        return $this->render ('sortie/creer_sortie.html.twig');
    }
}
