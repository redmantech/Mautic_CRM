<?php

namespace MauticPlugin\CustomCrmBundle\EventListener;

use Mautic\CalendarBundle\CalendarEvents;
use Mautic\CalendarBundle\Event\CalendarGeneratorEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Helper\DateTimeHelper;

class CalendarSubscriber extends CommonSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            CalendarEvents::CALENDAR_ON_GENERATE => array('onCalendarGenerate', 0),
        );
    }

    /**
     * Adds created/completed tasks to the calendar
     *
     * @param CalendarGeneratorEvent $event
     */
    public function onCalendarGenerate(CalendarGeneratorEvent $event)
    {
        $em = $this->factory->getEntityManager();
        $tasks = $em->getRepository('CustomCrmBundle:Task')->findAll();

        $router = $this->factory->getRouter();
        $events = array();
        // All tasks
        foreach ($tasks as $task) {
            $events[] = array(
                'start' => (new DateTimeHelper($task->getDateAdded()))->toLocalString(\DateTime::ISO8601),
                'url'   => $router->generate('mautic_lead_action', array('objectAction' => 'view', 'objectId' => $task->getLead()->getId()), true),
                'title' => $this->factory->getTranslator()->trans('ddi.lead_actions.tasks.calendar.created',
                    array(
                        '%name%' => $task->getName()
                    )
                ),
                'iconClass' => 'fa fa-fw fa-tasks'
            );
        }

        // Completed tasks
        foreach ($tasks as $task) {
            if ($task->getIsCompleted()) {
                $events[] = array(
                    'start' => (new DateTimeHelper($task->getDateCompleted()))->toLocalString(\DateTime::ISO8601),
                    'url'   => $router->generate('mautic_lead_action', array('objectAction' => 'view', 'objectId' => $task->getLead()->getId()), true),
                    'title' => $this->factory->getTranslator()->trans('ddi.lead_actions.tasks.calendar.completed',
                        array(
                            '%name%' => $task->getName()
                        )
                    ),
                    'iconClass' => 'fa fa-fw fa-check'
                );
            }
        }

        foreach ($this->factory->getModel('plugin.customCrm.opportunity')->getEntities() as $opportunity) {
            $events[] = array(
                'start' => (new DateTimeHelper($opportunity->getDateAdded()))->toLocalString(\DateTime::ISO8601),
                'url'   => $router->generate('mautic_customcrm_opportunity_action', array('objectAction' => 'edit', 'objectId' => $opportunity->getId()), true),
                'title' => $opportunity->getValue() . ' (' . $opportunity->getValueType() . ')',
                'iconClass' => 'fa fa-fw fa-dollar'
            );
        }
        $event->addEvents($events);
    }
}