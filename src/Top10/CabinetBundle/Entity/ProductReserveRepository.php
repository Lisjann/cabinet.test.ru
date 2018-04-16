<?php

namespace Top10\CabinetBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NativeQuery;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

use Top10\CabinetBundle\Form\Model\CatalogFilter;
use Top10\CabinetBundle\Entity;

class ProductReserveRepository extends EntityRepository
{
    /**
     * Удаляет все резервы определенного типа (шины, диски)
     *
     * @param $type
     * @return bool
     */
    public function removeByType($type)
    {
        if (($type != "tire") && ($type != "disk")) {
            return false;
        }
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $q = $qb->delete('Top10CabinetBundle:ProductReserve','pr')
            ->where($qb->expr()->like('pr.type', ':type'))
            ->setParameter('type',$type)
            ->getQuery();
        $q->execute();

        return true;
    }
}