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
    public function findAllForDtTableSorties($month, $year,$id_site, $id_user, $nom_sortie, $start,$end, $orga,$inscrit,$noninscrit,$sortiesPasse)
    {
        $conn = $this->getEntityManager()
            ->getConnection();
        $sql = "select * from sortie s left join sortie_utilisateur su on s.id = su.sortie_id ";
        $where = "WHERE strftime('%m', s.date_sortie) = '".$month."' 
            and strftime('%Y', s.date_sortie) = '".$year."'";

        if($id_site!= ""|| $nom_sortie!=""|| ($start !="" && $end != "") || $orga== "true"||$inscrit== "true"||$noninscrit== "true"||$sortiesPasse== "true")
        {
            $where = "where ";
            if($id_site != null){
                $where .= " s.id_lieu_id = ".$id_site." and";
            }
            if($nom_sortie != null){
                $where .= " s.nom like '%".$nom_sortie."%' and";
            }
            if($start !=null && $end != null){
                $where .= " s.date_sortie BETWEEN '".$start." 00:00:00' AND '".$end." 00:00:00' and";
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
            if($sortiesPasse == "true"){
                $where .= " s.date_sortie < DATE('now') and";
            }
                $where = substr($where,0,-3);
        }

        $sql = $sql . $where;
        $stmt = $conn->prepare($sql);

        return $stmt->execute()->fetchAll();
    }

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
