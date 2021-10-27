<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Utilisateur;
use App\Entity\Ville;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Form\SortieType;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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
        $repo = $entityManager->getRepository(Lieu::class);
        $lieux = $repo->findAll();


        return $this->render('sortie/index.html.twig', [
            'lieux' => $lieux,
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
        $user = $this->getDoctrine()->getRepository(Utilisateur::class)->find($this->getUser()->getId());
        if($user->getActif()) {
            if ($form->isSubmitted()) {

                $orga_session = $this->getUser()->getUsername();
                $organisateur = $this->getDoctrine()->getRepository(Utilisateur::class)->findOneBy(array('email' => $orga_session));
                $sortie->setOrganisateur($organisateur);
                $sortie->setEtat('En creation');
                $em = $this->getDoctrine()->getManager();
                $em->persist($sortie);
                $em->flush();
                $this->addFlash("success", "La sortie " . $sortie->getNom() . " prévue pour le " . $sortie->getDateSortie()->format("d/m/Y") . " a bien été créé");
                return $this->redirectToRoute('homepage');
            }

            return $this->render('sortie/creer_sortie.html.twig', [
                'form' => $form->createView()
            ]);
        }else{
            $this->addFlash("danger", "Vous n'êtes pas considéré comme un utilisateur actif !");
            return $this->redirectToRoute('homepage');
        }
    }


    /**
     * @Route ("/afficher/{id}", requirements={"id"="\d+"}, name="afficher_sortie")
     */
    public function afficher(Request $request, Sortie $sortie): Response{

        //Inutile car "Sortie $sortie" dans declaration de fonction
        //$sortie = $this->getDoctrine()->getRepository(Sortie::class)->find($id);
        $lieu = $this->getDoctrine()->getRepository(Lieu::class)->find($sortie->getIdLieu());
        $ville = $this->getDoctrine()->getRepository(Ville::class)->find($lieu->getIdVille());
        $month_ago = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
        if($sortie->getDateSortie()->format('Y-m-d')< $month_ago){
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
     * @Route("/data_lieu", name="data_lieu")
     */
    public function lieu_json(Request $request)
    {
        $id_lieu = $request->get('id_lieu');
        $entityManager = $this->getDoctrine();

        $repo = $entityManager->getRepository(Lieu::class);
        $lieu = $repo->find($id_lieu);

        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], [$encoder]);
        $resultat = $serializer->serialize($lieu, 'json');
        return new JsonResponse(json_decode($resultat));
    }

    /**
     * @Route("/data_ville", name="data_ville")
     */
    public function ville_json(Request $request)
    {
        $id_ville = $request->get('id_ville');
        $entityManager = $this->getDoctrine();
        $repo = $entityManager->getRepository(Ville::class);
        $ville = $repo->find($id_ville);

        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], [$encoder]);
        $resultat = $serializer->serialize($ville, 'json');
        return new JsonResponse(json_decode($resultat));
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
        $sorties_str = $repoS->findAllForDtTableSorties($id_site, $user->getId(), $nom_sortie, $start,$end, $orga,$inscrit,$noninscrit,$sortiesPasse);
        $sorties = array();
        foreach($sorties_str as $s){
            $users = array();
            $sortie = $repoS->find($s["id"]);
            foreach($sortie->getParticipants() as $u){
                array_push($users, $u);
            }
            if($noninscrit == "true" && $inscrit != "true" && !in_array($user, $users)){
                array_push($sorties, $sortie);
            }
            if($noninscrit != "true" && $inscrit == "true" && in_array($user, $users)){
                array_push($sorties, $sortie);
            }
            if($noninscrit != "true" && $inscrit != "true"){
                array_push($sorties, $sortie);
            }
        }

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
        $user = $this->getDoctrine()->getRepository(Utilisateur::class)->find($this->getUser()->getId());
        $path = 'sortie/modifier.html.twig';
        if($sortie->getOrganisateur()->getId() == $user->getId() && $user->getActif()) {
            if ($form->isSubmitted()) {
                $em = $this->getDoctrine()->getManager();
                if ($form->get('modifier')->isClicked()) {
                    $em->persist($sortie);
                    $this->addFlash("success", "Sortie modifié avec succès!");
                }
                if ($form->get('supprimer')->isClicked()) {
                    $em->remove($sortie);
                    $em->flush();
                    $this->addFlash("danger", "Sortie à été supprimé avec succès!");
                    return $this->redirectToRoute('homepage');
                }
                if ($form->get('publier')->isClicked()) {
                    $sortie->setEtat('Ouvert');
                    $em->persist($sortie);
                    $this->addFlash("success", "Sortie publié avec succès!");
                }
                if ($form->get('annuler')->isClicked()) {
                    $path = 'sortie/modifier.html.twig';
                    return $this->redirectToRoute('homepage');
                }
                $em->flush();
            }
            return $this->render($path, [
                'sortie' => $sortie,
                'form' => $form->createView()
            ]);
        }elseif($sortie->getOrganisateur()->getId() != $user->getId()){
            $this->addFlash("danger", "Vous n'êtes pas l'organisateur, vous ne pouvez pas annuler sa sortie!");
            return $this->redirectToRoute('homepage');
        }elseif(!$user->getActif()){
            $this->addFlash("danger", "Vous n'êtes pas un utilisateur actif!");
            return $this->redirectToRoute('homepage');
        }
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route ("/annuler/{id}", requirements={"id"="\d+"}, name="annuler_sortie")
     */
    public function annuler(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $sortie = $em->getRepository(Sortie::class)->find($request->get('id'));
        $user = $em->getRepository(Utilisateur::class)->find($this->getUser()->getId());
        if($sortie->getOrganisateur()->getId() == $user->getId() && $user->getActif()) {
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
        }elseif($sortie->getOrganisateur()->getId() != $user->getId()){
            $this->addFlash("danger", "Vous n'êtes pas l'organisateur, vous ne pouvez pas annuler sa sortie!");
            return $this->redirectToRoute('homepage');
        }elseif(!$user->getActif()){
            $this->addFlash("danger", "Vous n'êtes pas un utilisateur actif!");
            return $this->redirectToRoute('homepage');
        }
    }
    /**
     * @Route ("/participer/{id}", requirements={"id"="\d+"}, name="participer_sortie")
     */
    public function participer(Request $request, Sortie $sortie)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $user = $this->getDoctrine()->getRepository(Utilisateur::class)->find($user->getId());
        if($user->getActif() && $sortie->getDateFinInscription()->format('d-m-Y') > date('d-m-Y')){
            if(count($sortie->getParticipants()) + 1 <= $sortie->getNbPlace()){
                $sortie->addParticipant($user);
                $em->persist($sortie);
                $em->flush();
                $this->addFlash('success', 'Vous faites partie des participants de la sortie : '. $sortie->getNom()."!");
            }else{
                $this->addFlash('danger', 'Aucune place disponible pour la sortie : '. $sortie->getNom()."!");

            }
        }elseif($sortie->getDateFinInscription()->format('d-m-Y') < date('d-m-Y')){
            $this->addFlash("danger", "Vous ne pouvez plus vous inscrire!");

        }
        else{
            $this->addFlash("danger", "Vous n'êtes pas un utilisateur actif!");
        }

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route ("/desister/{id}", requirements={"id"="\d+"}, name="desister_sortie")
     */
    public function desister(Request $request, Sortie $sortie)
    {

        $em = $this->getDoctrine()->getManager();
        $user_session = $this->getUser();
        $user = $this->getDoctrine()->getRepository(Utilisateur::class)->find($user_session->getId());
        if($user->getActif()){
            if(!$sortie->getParticipants()->contains('id', $user->getId())){
                $sortie->removeParticipant($user);
                $em->persist($sortie);
                $em->flush();
                $this->addFlash('success', 'Vous ne faites plus partie des participants de la sortie : '. $sortie->getNom()."!");
            }
        }else{
            $this->addFlash('danger', "Vous n'êtes pas un utilisateur actif !");
        }

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route ("/publish/{id}", requirements={"id"="\d+"}, name="publier_sortie")
     */
    public function publier(Request $request, Sortie $sortie)
    {

        $em = $this->getDoctrine()->getManager();
        $sortie->setEtat("Ouvert");
        $user_session = $this->getUser();
        if($sortie->getOrganisateur()->getId() == $user_session->getId())
        {
            $em->persist($sortie);
            $em->flush();
        }

        return $this->redirectToRoute('homepage');
    }

}
