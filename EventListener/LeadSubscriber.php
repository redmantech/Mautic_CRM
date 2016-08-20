<?php

namespace MauticPlugin\CustomCrmBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\LeadBundle\Event\LeadTimelineEvent;
use Mautic\LeadBundle\LeadEvents;

class LeadSubscriber extends CommonSubscriber
{
    static public function getSubscribedEvents()
    {
        return array(
            LeadEvents::TIMELINE_ON_GENERATE => array('onTimeLineGenerate', 0)
        );
    }

    public function onTimeLineGenerate(LeadTimelineEvent $event)
    {
        $this->addOpportunityEvents($event);
        $this->addTaskEvents($event);

    }

    private function addOpportunityEvents(LeadTimelineEvent $event)
    {
        $model = $this->factory->getModel('customcrm.opportunity');

        /** @var \MauticPlugin\CustomCrmBundle\Entity\Opportunity[] $opportunities */
        $opportunities = $model->getRepository()->findByLead($event->getLead());

        foreach ($opportunities as $opportunity) {
            $event->addEvent(array(
                'event' => 'opportunity.created',
                'eventLabel' => 'Opportunity created',
                'eventType' => 'Opportunity created',
                'timestamp' => $opportunity->getDateAdded(),
                'extra' => array(
                    'opportunity' => '<a href="'. $this->factory->getRouter()->generate('mautic_customcrm_opportunity_action', array(
                            'objectAction' => 'edit',
                            'objectId' => $opportunity->getId()
                        )) .'"> $' . number_format($opportunity->getValue()) . '</a>'
                ),
                'contentTemplate' => 'CustomCrmBundle:Opportunity\Timeline:created.html.php'
            ));
        }
    }

    private function addTaskEvents(LeadTimelineEvent $event)
    {
        $eventTypeKeyCreated = 'task.created';
        $eventTypeKeyCompleted = 'task.completed';

        $event->addEventType($eventTypeKeyCreated, $this->translator->trans('ddi.lead_actions.tasks.timeline.filter.created'));
        $event->addEventType($eventTypeKeyCompleted, $this->translator->trans('ddi.lead_actions.tasks.timeline.filter.completed'));

        $em = $this->factory->getEntityManager();
        $query = $em->getRepository('CustomCrmBundle:Task')->createQueryBuilder('t');

        $query->where('t.lead = :lead')
            ->setParameter('lead', $event->getLead());

        $filters = $event->getEventFilters();
        if (isset($filters['search']) && $filters['search']) {
            $query
                ->andWhere('t.name LIKE :name')
                ->setParameter('name', '%' . $filters['search'] . '%');
        }

        $tasks = $query->getQuery()->getResult();

        if ($event->isApplicable($eventTypeKeyCreated)) {
            foreach ($tasks as $task) {
                $event->addEvent(array(
                    'event' => $eventTypeKeyCreated,
                    'eventLabel' => 'Task created',
                    'eventType' => 'Task created',
                    'timestamp' => $task->getDateAdded(),
                    'extra' => array(
                        'name' => $task->getName()
                    ),
                    'contentTemplate' => 'CustomCrmBundle:Task\Timeline:created.html.php'
                ));
            }
        }

        // Completed tasks
        if ($event->isApplicable($eventTypeKeyCompleted)) {
            foreach ($tasks as $task) {
                if ($task->getIsCompleted()) {

                    $event->addEvent(array(
                        'event' => $eventTypeKeyCompleted,
                        'eventLabel' => 'task',
                        'timestamp' => $task->getDateCompleted(),
                        'extra' => array(
                            'name' => $task->getName()
                        ),
                        'contentTemplate' => 'CustomCrmBundle:Task\Timeline:completed.html.php'
                    ));
                }
            }
        }
    }
}