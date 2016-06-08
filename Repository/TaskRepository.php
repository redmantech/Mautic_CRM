<?php

namespace MauticPlugin\CustomCrmBundle\Repository;

use Mautic\CoreBundle\Entity\CommonRepository;
use MauticPlugin\CustomCrmBundle\Entity\Task;

class TaskRepository extends CommonRepository
{
    /**
     * @param \DateTime $date
     * @return Task[]
     */
    public function getOpenTasks(\DateTime $date)
    {
        $q = $this->getEntityManager()->createQueryBuilder()
            ->from('CustomCrmBundle:Task', 't')
            ->select('t, l, u')
            ->leftJoin('t.lead', 'l')
            ->leftJoin('t.assignUser', 'u')
            ->where('t.dueDate >= :start_datetime')
            ->andWhere('t.dueDate <= :end_datetime')
            ->andWhere('t.isCompleted = :is_completed')
            ->andWhere('t.notified = :notified')
            ->setParameters(
                array(
                    'start_datetime' => $date->format('Y-m-d 00:00:00'),
                    'end_datetime' =>  $date->format('Y-m-d 23:59:59'),
                    'is_completed' => false,
                    'notified' => false,
                )
            );

        return $q->getQuery()->getResult();
    }
}
