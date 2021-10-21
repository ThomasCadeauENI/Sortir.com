<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Utilisateur;
use App\Entity\Ville;
use App\Repository\UtilisateurRepository;
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

            $orga_session = $this->getUser()->getUsername();
            $organisateur = $this->getDoctrine()->getRepository(Utilisateur::class)->findOneBy(array('email' => $orga_session));
            $sortie->setOrganisateur($organisateur);
            $sortie->setEtat('NC');
            $sortie->addParticipant($organisateur);
            $em = $this->getDoctrine()->getManager();
            $em->persist($sortie);
            $em->flush();
        }

        return $this->render('sortie/creer_sortie.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/afficher/{id}', 'afficher')]
    public function afficher(Request $request, Sortie $sortie, UtilisateurRepository $UR): Response{

        //Inutile car "Sortie $sortie" dans declaration de fonction
        //$sortie = $this->getDoctrine()->getRepository(Sortie::class)->find($id);
        $lieu = $this->getDoctrine()->getRepository(Lieu::class)->find($sortie->getIdLieu());
        $ville = $this->getDoctrine()->getRepository(Ville::class)->find($lieu->getIdVille());

        /*
         * Participants []
         * For utilisateur in sortie_utilisateur where S.id_sortie=U.id_sortie
         *      Participants.add(utilisateur)
         *
         */

        $participants = $sortie->getParticipants();

        return $this->render('sortie/affiche.html.twig', [
            'sortie' => $sortie,
            'lieu' => $lieu,
            'ville' => $ville,
            'participants' => $participants
        ]);
    }

    #[Route('/profil', 'profil')]
    public function profil(){
        return $this->render ('utilisateur/profil.html.twig');
    }

    #[Route('/afficher/{id}', 'afficher')]
    public function participer(){

        return $this->render('sortie/affiche.html.twig');
    }
}