<?php

namespace App\Controller;

use App\Entity\Ville;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
{

    /**
     * @Route ("/gestion_ville", name="gestion_ville")
     */    public function index(): Response
    {
        return $this->render('ville/index.html.twig', [
            'controller_name' => 'VilleController',
        ]);
    }

    /**
     * @Route ("/afficher_DtTableVilles", name="afficher_DtTableVilles")
     */
    public function DtTableVilles(){
        $entityManager = $this->getDoctrine();
        $repo = $entityManager->getRepository(Ville::class);

        $villes = $repo->findAll();

        return $this->render('partialView/DtTableVilles.html.twig', [
            'villes' => $villes,
        ]);
    }

    /**
     * @Route ("/add_ville", name="add_ville")
     */
    public function add_ville(Request $request): Response
    {
        $nom_ville = $request->get('nom');
        if($nom_ville != "" && trim($nom_ville, " ") != "")
        {
            $ville = new Ville();
            $ville->setNom($nom_ville);
            $em = $this->getDoctrine()->getManager();
            $em->persist($ville);
            $em->flush();
        }
        return $this->redirect('gestion_ville');
    }
    /**
     * @Route ("/remove_ville", name="remove_ville")
     */
    public function remove_ville(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $ville = $entityManager->getRepository(Ville::class)->find($id);
        if($ville){
            $entityManager->remove($ville);
            $entityManager->flush();
        }

        return $this->redirect('gestion_ville');
    }


    /**
     * @Route ("/update_ville", name="update_ville")
     */
    public function update_ville(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $id = $request->get('id');
        $ville = $entityManager->getRepository(Ville::class)->find($id);

        $name = $request->get('nom');

        if (!$ville) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $ville->setNom($name);
        $entityManager->flush();

        return $this->redirect('gestion_ville');

    }

}
