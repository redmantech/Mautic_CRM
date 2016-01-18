<?php

namespace MauticPlugin\CustomCrmBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\ReportBundle\Event\ReportBuilderEvent;
use Mautic\ReportBundle\Event\ReportGeneratorEvent;
use Mautic\ReportBundle\ReportEvents;

class ReportSubscriber extends CommonSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            ReportEvents::REPORT_ON_BUILD => array('onReportBuilder', 0),
            ReportEvents::REPORT_ON_GENERATE => array('onReportGenerate', 0)
        );
    }

    public function onReportBuilder(ReportBuilderEvent $event)
    {
        if ($event->checkContext(array('task'))) {
            // Task fields
            $prefix = 't.';
            $columns = array(
                $prefix . 'id' => array(
                    'label' => 'mautic.core.id',
                    'type'  => 'id'
                ),
                $prefix . 'name' => array(
                    'label' => 'ddi.lead_actions.tasks.report.field.name',
                    'type'  => 'string'
                ),
                $prefix . 'date_added' => array(
                    'label' => 'mautic.report.field.date_added',
                    'type'  => 'datetime'
                ),
                $prefix . 'date_completed' => array(
                    'label' => 'ddi.lead_actions.tasks.report.field.date_completed',
                    'type'  => 'datetime'
                ),
                $prefix . 'due_date' => array(
                    'label' => 'ddi.lead_actions.tasks.report.field.due_date',
                    'type'  => 'datetime'
                ),
                $prefix . 'is_completed' => array(
                    'label' => 'ddi.lead_actions.tasks.report.field.is_completed',
                    'type'  => 'int'
                ),
            );

            // Assign user fields
            $userPrefix = 'u.';
            $userColumns = array(
                $userPrefix . 'id' => array(
                    'label' => 'Assigned user ID',
                    'type' => 'int'
                ),
                $userPrefix . 'first_name' => array(
                    'label' => 'Assigned user first name',
                    'type' => 'string'
                ),
                $userPrefix . 'last_name' => array(
                    'label' => 'Assigned user last name',
                    'type' => 'string'
                )
            );

            $event->addTable('task', array(
                'display_name' => 'Tasks',
                'columns' => array_merge($columns, $event->getLeadColumns(), $userColumns)
                )
            );
        }

        if ($event->checkContext(array('opportunity'))) {
            $prefix = 'op.';
            $columns = array(
                $prefix . 'status' => array(
                    'label' => 'Status',
                    'type' => 'string'
                ),
                $prefix . 'confidence' => array(
                    'label' => 'Confidence',
                    'type' => 'int'
                ),
                $prefix . 'value' => array(
                    'label' => 'Value',
                    'type' => 'int'
                ),
                $prefix . 'value_type' => array(
                    'label' => 'Type',
                    'type' => 'string'
                ),
                $prefix . 'estimated_close' => array(
                    'label' => 'Estimated Close',
                    'type' => 'datetime'
                ),
                $prefix . 'comments' => array(
                    'label' => 'Comments',
                    'type' => 'string'
                )
            );

            // Assign user fields
            $userPrefix = 'u.';
            $userColumns = array(
                $userPrefix . 'id' => array(
                    'label' => 'Assigned user ID',
                    'type' => 'int'
                ),
                $userPrefix . 'first_name' => array(
                    'label' => 'Assigned user first name',
                    'type' => 'string'
                ),
                $userPrefix . 'last_name' => array(
                    'label' => 'Assigned user last name',
                    'type' => 'string'
                )
            );

            $event->addTable('opportunity', array(
                    'display_name' => 'Opportunities',
                    'columns' => array_merge($columns, $event->getLeadColumns(), $userColumns)
                )
            );
        }
    }

    public function onReportGenerate(ReportGeneratorEvent $event)
    {
        $context = $event->getContext();
        if ($context === 'task') {
            $queryBuilder = $this->factory->getDatabase()->createQueryBuilder();
            $queryBuilder->from('tasks', 't')->leftJoin('t', 'users', 'u', 'u.id = t.assign_user_id');
            $event->addLeadLeftJoin($queryBuilder, 't');
            $event->setQueryBuilder($queryBuilder);
        }

        if ($context === 'opportunity') {
            $queryBuilder = $this->factory->getDatabase()->createQueryBuilder();
            $queryBuilder->from('opportunities', 'op')->leftJoin('op', 'users', 'u', 'u.id = op.owner_user_id');
            $event->addLeadLeftJoin($queryBuilder, 'op');
            $event->setQueryBuilder($queryBuilder);
        }
    }
}