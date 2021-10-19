<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

class SortieController extends AbstractController
{
    #[Route('/sortie', 'sortie')]
    public function index(): Response
    {
        return $this->render('sortie/index.html.twig', [
            'controller_name' => 'SortieController',
        ]);
    }

    #[Route('/affiche', 'affiche')]
    public function affiche(): Response
    {
        $sortie = new Sortie();
        $user = new Utilisateur();
        $user->setPrenom("qsd");

        $sortie->setDateSortie(new \DateTime());
        $sortie->setDateFinInscription(new \DateTime());
        $sortie->setDescription("SDqqsdqdq");
        $sortie->setNbPlace(321);


        return $this->render('sortie/affiche.html.twig', [
            'user' => $user,
            'sortie' => $sortie
        ]);
    }
}
