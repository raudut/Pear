<?php

namespace App\Repository;

use App\Entity\Product;
use App\Data\SearchData;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{

    public function findProductByStatut($statut)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p')
            ->where('p.statut LIKE :statut')
            ->setParameter('statut', '%"'.$statut.'"%');

        return $qb->getQuery()->getResult();
    }

    public function findByUserId($brandId)
{
    $qb = $this->createQueryBuilder('rm');
    $qb->where('IDENTITY(rm.brand) = :brandId')
       ->setParameter('brandId', $brandId);

    return $qb->getQuery()->getResult();
}
    
    public function findProductByIdUser($owner)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p')
            ->where('p.owner LIKE :owner')
            ->setParameter('owner', '%"'.$owner.'"%');

        return $qb->getQuery()->getResult();
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }


/**
     * Récupère les produits en lien avec une recherche
     * @return Product[]
     */
    public function findSearch(SearchData $data): array{
        $query = $this
        ->createQueryBuilder('p')
        ->select('c', 'p')
        ->join('p.categorie', 'c');

    if (!empty($data->q)) {
        $query = $query
            ->andWhere('p.nom LIKE :q')
            ->setParameter('q', "%{$data->q}%");
    }

    if (!empty($data->min)) {
        $query = $query
            ->andWhere('p.prix >= :min')
            ->setParameter('min', $data->min);
    }

    if (!empty($data->max)) {
        $query = $query
            ->andWhere('p.prix <= :max')
            ->setParameter('max', $data->max);
    }


    if (!empty($data->categorie)) {
        $query = $query
            ->andWhere('c.id IN (:categorie)')
            ->setParameter('categorie', $data->categorie);
    }

      
   return $query -> getQuery()->getResult();
    }
}
