<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    /**
    * @return Sortie[] Returns an array of Sortie objects
    */
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Sortie[] Returns an array of Sortie objects
     */
    public function findAllForDtTableSorties($id_site, $id_user, $nom_sortie, $start,$end, $orga,$inscrit,$noninscrit,$sortiesPasse)
    {
        $conn = $this->getEntityManager()
            ->getConnection();
        $sql = "select distinct id, id, id_ville_id, id_lieu_id, organisateur_id, date_sortie, date_fin_inscription, nb_place, duree, description, nom, etat, motif  from sortie s left join sortie_utilisateur su on s.id = su.sortie_id ";
        $last_mois = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
        $where = "WHERE s.date_sortie > '".$last_mois."'";

        if($id_site!= ""|| $nom_sortie!=""|| ($start !="" && $end != "") || $orga== "true"||$inscrit== "true"||$noninscrit== "true"||$sortiesPasse== "true")
        {
            $where .= " and ";
            if($sortiesPasse == "true"){
                $where = "where ";
                $where .= " s.date_sortie < DATE('now') and";
            }
            if($id_site != null){
                $where .= " s.id_lieu_id = ".$id_site." and";
            }
            if($nom_sortie != null){
                $where .= " s.nom like '%".$nom_sortie."%' and";
            }
            if($start !=null && $end != null){
                $where .= " s.date_sortie BETWEEN '".$start." 00:00:00' AND '".$end." 23:59:59' and";
            }
            if($orga == "true"){
                $where .= " s.organisateur_id = ".$id_user."  and";
            }
            if($inscrit == "true"){
                $where .= " su.utilisateur_id = ".$id_user." and";
            }
            if($noninscrit == "true"){
                $where .= " su.utilisateur_id <>".$id_user." and";
            }

                $where = substr($where,0,-3);
        }

        $sql = $sql . $where;
        //dd($sql);
        $stmt = $conn->prepare($sql);

        return $stmt->execute()->fetchAll();

    }

}
