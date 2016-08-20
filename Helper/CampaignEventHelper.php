<?php

namespace MauticPlugin\CustomCrmBundle\Helper;

use Mautic\CoreBundle\Factory\MauticFactory;
use MauticPlugin\CustomCrmBundle\Entity\Opportunity;

class CampaignEventHelper
{
    static public function createOpportunity(MauticFactory $factory, $lead, $event)
    {
        $model = $factory->getModel('customCrm.opportunity');
        $opportunity = new Opportunity();
        $opportunity->setStatus($event['properties']['status']);
        $opportunity->setConfidence($event['properties']['confidence']);
        $opportunity->setValue($event['properties']['value']);
        $opportunity->setValueType($event['properties']['valueType']);
        $opportunity->setEstimatedClose(self::generateDueDate($event));
        $opportunity->setComments($event['properties']['comments']);
        $opportunity->setOwnerUser($factory->getModel('user')->getEntity($event['properties']['ownerUser']));
        $opportunity->setLead($lead);
        $model->saveEntity($opportunity);
    }

    public static function addTaskAction(MauticFactory $factory, $lead, $event)
    {
        $sql = 'INSERT INTO tasks (due_date, name, lead_id, assign_user_id, is_completed, date_added) VALUES (?, ?, ?, ?, ?, ?)';

        $stmt = $factory->getDatabase()->prepare($sql);

        $dueDate = self::generateDueDate($event);

        $stmt->bindValue(1, $dueDate->format('Y-m-d H:i:s'));
        $stmt->bindValue(2, $event['properties']['name']);
        $stmt->bindValue(3, $lead->getId());
        $stmt->bindValue(4, $event['properties']['assignUser']);
        $stmt->bindValue(5, 0);
        $dateTime = new \DateTime('now');
        $stmt->bindValue(6, $dateTime->format('Y-m-d H:i:s'));

        $stmt->execute();

        return true;
    }

    private static function generateDueDate($event)
    {
        // Generate due date
        $dueDate = new \DateTime('now');
        $dateInterval = $event['properties']['dateInterval'];
        $dateIntervalUnit = $event['properties']['dateIntervalUnit'];
        $period = 'P';
        switch ($dateIntervalUnit) {
            case 'i':
                $period .= sprintf('T%dM', $dateInterval);
                break;
            case 'h':
                $period .= sprintf('T%dH', $dateInterval);
                break;
            case 'd':
                $period .= sprintf('%dD', $dateInterval);
                break;
            case 'm':
                $period .= sprintf('%dM', $dateInterval);
                break;
            case 'y':
                $period .= sprintf('%dY', $dateInterval);
        }
        $dueDate->add(new \DateInterval($period));

        return $dueDate;
    }
}
