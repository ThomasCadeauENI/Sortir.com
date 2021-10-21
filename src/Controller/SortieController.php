<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Utilisateur;
use App\Entity\Ville;
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

        $entityManager = $this->getDoctrine();
        $repo = $entityManager->getRepository(Ville::class);
        $villes = $repo->findAll();


        return $this->render('sortie/index.html.twig', [
            'villes' => $villes,
            'date' => date('d/m/Y')
        ]);
    }


    /**
     * @Route ("/creer", name="creer")
     */
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


    /**
     * @Route ("/afficher/{id}", requirements={"id"="\d+"}, name="afficher_sortie")
     */
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

    /**
     * @Route ("/afficher_DtTableSorties", name="afficher_DtTableSorties")
     */
    public function DtTableSorties(Request $request){
        $entityManager = $this->getDoctrine();
        $repoS = $entityManager->getRepository(Sortie::class);
        $repoU = $entityManager->getRepository(Utilisateur::class);

        $id_site = $request->get('id_site');
        $id_user = $request->get('id_user');
        $nom_sortie = $request->get('nom_sortie');
        $start = $request->get('start');
        $end = $request->get('end');
        $orga = $request->get('orga');
        $inscrit = $request->get('inscrit');
        $noninscrit = $request->get('noninscrit');
        $sortiesPasse = $request->get('sortiesPasse');

        $user = $this->getUser();
        $sorties = $repoS->findAllForDtTableSorties((int) date('m'), (int) date('Y'), $id_site, $user->getId(), $nom_sortie, $start,$end, $orga,$inscrit,$noninscrit,$sortiesPasse);

        return $this->render('partialView/DtTableSorties.html.twig', [
            'sorties' => $sorties,
            'user' => $user
        ]);

}

}
