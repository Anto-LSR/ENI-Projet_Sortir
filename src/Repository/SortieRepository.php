<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

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
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Sortie $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Sortie $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function selectByFilters($user, $recherche, $site, $dateDebut, $dateFin, $jeSuisOrganisateur, $jeSuisInscrit, $jeSuisPasInscrit)
    {

        $qb = $this->createQueryBuilder('s');
        $qb->join("s.organisateur", "o");

        //Condition : affichage selon le site choisi rattaché à l'organisateur de l'évènement avec le join
            if(trim($site) != null && $site != "all"){

                $qb->andWhere($qb->expr()->eq('o.site', ':site'))
                ->setParameter('site', $site);
            }
        //Condition : filtre sur le contenu du nom de la sortie
            if(trim($recherche) != null) {
                $qb->andWhere($qb->expr()->like('s.nom', ':search'))
                ->setParameter('search', '%'.trim($recherche).'%');
            }

        //Condition : filtre sur la date de début sélectionnée qui doit etre inférieure à la date de début
        if($dateDebut != null && $dateFin == null) {
                $qb->andWhere('s.dateHeureDebut  >= :dateHD')
                ->setParameter('dateHD', $dateDebut);
            }

        //Condition : filtre sur la date de début sélectionnée qui doit etre inférieure à la date de fin
        if($dateFin != null && $dateDebut == null) {
            $qb->andWhere('s.dateHeureDebut <= :dateF')
                ->setParameter('dateF',$dateFin);

        }
        // filtre pour sélectionner une sortie entre les deux dates choisies
        if($dateFin != null && $dateDebut != null) {
            $qb->andWhere('s.dateHeureDebut  >= :dateHD')
                ->setParameter('dateHD', $dateDebut);
            $qb->andWhere('s.dateHeureDebut <= :dateF')
                ->setParameter('dateF', $dateFin);
        }

        // filtre pour sélectionner les sorties dont je suis l'organisateur
        if($jeSuisOrganisateur != null) {
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

        // filtre pour sélectionner les sorties auxquelles je suis inscrit
        if($jeSuisInscrit != null) {
            $qb->andWhere(':inscrit MEMBER OF s.participants')
                ->setParameter('inscrit', $user);
        }

        // filtre pour sélectionner les sorties auxquelles je ne suis pas inscrit
        if($jeSuisPasInscrit != null) {
            $qb->andWhere(':inscrit NOT MEMBER OF s.participants')
                ->setParameter('inscrit', $user);
        }

        return $qb->getQuery()->getResult();

    }

    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
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
    */

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
