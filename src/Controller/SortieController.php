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

        if ($form->isSubmitted()) {

            $orga_session = $this->getUser()->getUsername();
            $organisateur = $this->getDoctrine()->getRepository(Utilisateur::class)->findOneBy(array('email' => $orga_session));
            $sortie->setOrganisateur($organisateur);
            $sortie->setEtat('En creation');
            $sortie->addParticipant($organisateur);
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
    public function afficher(Request $request, Sortie $sortie): Response{

        //Inutile car "Sortie $sortie" dans declaration de fonction
        //$sortie = $this->getDoctrine()->getRepository(Sortie::class)->find($id);
        $lieu = $this->getDoctrine()->getRepository(Lieu::class)->find($sortie->getIdLieu());
        $ville = $this->getDoctrine()->getRepository(Ville::class)->find($lieu->getIdVille());
        if(strtotime($sortie->getDateSortie()->format('Y-m-d'))< date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) )){
            return $this->redirectToRoute('homepage');
        }

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

    /**
     * @Route ("/data_lieu/", name="data_lieu")
     */
    public function data_lieu(Response $response)
    {
        $id_lieu = $response->get('id_lieu');
        $entityManager = $this->getDoctrine();
        $repo = $entityManager->getRepository(Lieu::class);
        $lieu = $repo->find($id_lieu);
        return array($lieu->getRue(), $lieu->getCodePostal());
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
        $sorties = $repoS->findAllForDtTableSorties($id_site, $user->getId(), $nom_sortie, $start,$end, $orga,$inscrit,$noninscrit,$sortiesPasse);
        $utilisateurs = $repoU->findAll();
        return $this->render('partialView/DtTableSorties.html.twig', [
            'sorties' => $sorties,
            'user' => $user,
            'utilisateurs' => $utilisateurs,
        ]);

    }

    /**
     * @Route ("/modifier/{id}", requirements={"id"="\d+"}, name="modifier_sortie")
     */
    public function modifier(Request $request, Sortie $sortie){

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        $path = 'sortie/modifier.html.twig';

        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            if ($form->get('modifier')->isClicked()){
                $em->persist($sortie);
            }
            if ($form->get('supprimer')->isClicked()) {
                $em->remove($sortie);
            }
            if ($form->get('publier')->isClicked()){
                $sortie->setEtat('Ouvert');
                $em->persist($sortie);
            }
            if ($form->get('annuler')->isClicked()) {
                $path = 'sortie/modifier.html.twig';
            }
            $em->flush();
        }

        return $this->render($path, [
            'sortie' => $sortie,
            'form' => $form->createView()
        ]);
    }




    /**
     * @Route ("/annuler/{id}", requirements={"id"="\d+"}, name="annuler_sortie")
     */
    public function annuler(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $sortie = $em->getRepository(Sortie::class)->find($request->get('id'));
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $sortie->setEtat("Annulée");
            $em->persist($sortie);
            $em->flush();
            return $this->redirectToRoute('homepage');
        }

        return $this->render('sortie/annuler.html.twig', [
            'sortie' => $sortie,
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route ("/inscription/{id}",name="inscription_sortie")
     */
    public function inscription(Request $request, Sortie $sortie)
    {

        return $this->render('sortie/annuler.html.twig', [
            'sortie' => $sortie,

        ]);
    }
}

