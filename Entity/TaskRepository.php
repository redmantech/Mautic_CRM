<?php

namespace MauticPlugin\CustomCrmBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

class TaskRepository extends CommonRepository
{
    /**
     * @param $leadId
     * @return integer
     */
    public function getCompletedCount($leadId)
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t)')
            ->where('t.lead = :id')
            ->andWhere('t.isCompleted = 0')
            ->setParameter('id', $leadId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
