<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends AbstractController
{

    /**
     * @Route ("/gestion_lieu", name="gestion_lieu")
     */
    public function index(): Response
    {
        return $this->render('lieu/index.html.twig', [
            'controller_name' => 'LieuController',
        ]);
    }
    /**
     * @Route ("/remove_lieu", name="remove_lieu")
     */
    public function remove_lieu(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $lieu = $entityManager->getRepository(Lieu::class)->find($id);
        if($lieu){
            $entityManager->remove($lieu);
            $entityManager->flush();
        }

        return $this->redirect('gestion_lieu');
    }

    /**
     * @Route ("/afficher_DtTableLieux", name="afficher_DtTableLieux")
     */
    public function DtTableLieux(){
        $entityManager = $this->getDoctrine();
        $repo = $entityManager->getRepository(Lieu::class);
        $entityManager = $this->getDoctrine();
        $repoV = $entityManager->getRepository(Ville::class);
        $villes = $repoV->findAll();
        $lieux = $repo->findAll();
        return $this->render('partialView/DtTableSites.html.twig', [
            'lieux' => $lieux,
            'villes' => $villes,
        ]);
    }
    /**
     * @Route ("/add_lieu", name="add_lieu")
     */
    public function addLieu(Request $request): Response
    {
        $nom = $request->get('nom');
        $cp = $request->get('cp');
        $rue = $request->get('rue');
        $idv = $request->get('idv');
        $longitude = $request->get('longitude');
        $latitude = $request->get('latitude');


        $entityManager = $this->getDoctrine()->getManager();
        $ville = $entityManager->getRepository(Ville::class)->find($idv);


        if(trim($nom, " ") != "" &&trim($cp, " ") != ""&&intval($cp)>0 &&trim($rue, " ") != "" && $ville)
        {
            $lieu = new Lieu();
            $lieu->setNom($nom);
            $lieu->setCodePostal($cp);
            $lieu->setRue($rue);
            $lieu->setIdVille($ville);
            if($longitude != ""){
                $lieu->setLongitude($longitude);
            }
            if($latitude != ""){
                $lieu->setLongitude($latitude);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($lieu);
            $em->flush();
        }
        return $this->redirect('gestion_lieu');
    }
    /**
     * @Route ("/update_lieu", name="update_lieu")
     */
    public function update_lieu(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $id = $request->get('id');

        $lieu = $entityManager->getRepository(Lieu::class)->find($id);

        $nom = $request->get('nom');
        $rue = $request->get('r');
        $cp = $request->get('cp');
        $idv = $request->get('idv');
        $lat = $request->get('lat');
        $lon = $request->get('lon');



        if (!$lieu) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $lieu->setNom($nom);
        $lieu->setRue($rue);
        $lieu->setCodePostal($cp);

        $ville = $entityManager->getRepository(Ville::class)->find($idv);
        $lieu->setIdVille($ville);
        $lieu->setLatitude($lat);
        $lieu->setLongitude($lon);
        $entityManager->flush();

        return $this->redirect('gestion_lieu');

    }
}
