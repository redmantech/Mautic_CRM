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
    public function getOpenTasks(\DateTime $endDate)
    {
        $startDate = new \DateTime();
        $startDate->modify('monday this week');
        $currentDate = new \DateTime();

        $q = $this->getEntityManager()->createQueryBuilder()
            ->from('CustomCrmBundle:Task', 't')
            ->select('t, l, u')
            ->leftJoin('t.lead', 'l')
            ->leftJoin('t.assignUser', 'u')
            ->where('t.dueDate >= :start_datetime')
            ->andWhere('t.dueDate <= :end_datetime')
            ->andWhere('t.isCompleted = :is_completed')
            ->andWhere('t.notifiedDate is null or t.notifiedDate <> :notified_date')
            ->setParameters(
                array(
                    'start_datetime' => $startDate->format('Y-m-d 00:00:00'),
                    'end_datetime' =>  $endDate->format('Y-m-d 23:59:59'),
                    'is_completed' => false,
                    'notified_date' => $currentDate->format('Y-m-d'),
                )
            );

        return $q->getQuery()->getResult();
    }
}
