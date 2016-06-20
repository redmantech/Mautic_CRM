<?php

return array(
    'name'        => 'Redman Mautic CRM',
    'description' => 'Task & Opportunity for mautic',
    'author'      => 'Redman Technologies Inc',
    'version'     => '1.0.4',

    'routes'   => array(
        'main' => array(
            'mautic_customcrm_opportunity_index'        => array(
                'path'       => '/opportunities/{page}',
                'controller' => 'CustomCrmBundle:Opportunity:index'
            ),
            'mautic_customcrm_opportunity_action'       => array(
                'path'       => '/opportunities/{objectAction}/{objectId}',
                'controller' => 'CustomCrmBundle:Opportunity:execute'
            ),

            'ddi_lead_actions_task_index' => array(
                'path' => '/tasks',
                'controller' => 'CustomCrmBundle:Task:index'
            ),
            'mautic_task_action' => array(
                'path' => '/tasks/{objectAction}/{objectId}',
                'controller' => 'CustomCrmBundle:Task:executeTask',
                'requirements' => array(
                    'objectId' => '\d+'
                )
            ),
        )
    ),

    'services' => array(
        'forms'   => array(
            'customcrm.form.type.opportunity' => array(
                'class'     => 'MauticPlugin\CustomCrmBundle\Form\Type\OpportunityType',
                'arguments' => 'mautic.factory',
                'alias'     => 'customcrm_opportunity'
            ),
            'customcrm.form.type.opportunity.campaign_builder' => array(
                'class'     => 'MauticPlugin\CustomCrmBundle\Form\Type\CampaignOpportunityType',
                'arguments' => 'mautic.factory',
                'alias'     => 'customcrm_campaign_opportunity'
            ),

            'ddi.lead_actions.form.type.task' => array(
                'class' => 'MauticPlugin\CustomCrmBundle\Form\Type\TaskType',
                'arguments' => 'mautic.factory',
                'alias' => 'task'
            )
        ),

        'events' => array(
            'customcrm.opportunity.lead.subscriber' => array(
                'class' => 'MauticPlugin\CustomCrmBundle\EventListener\LeadSubscriber'
            ),
            'customcrm.opportunity.campaignbuilder.subscriber' => array(
                'class' => 'MauticPlugin\CustomCrmBundle\EventListener\CampaignSubscriber'
            ),

            'ddi.lead_actions.calendar.subscriber' => array(
                'class' => 'MauticPlugin\CustomCrmBundle\EventListener\CalendarSubscriber'
            ),
            'ddi.lead_actions.report.subscriber' => array(
                'class' => 'MauticPlugin\CustomCrmBundle\EventListener\ReportSubscriber'
            )
        )
    ),

    'menu'     => array(
        'main' => array(
            'priority' => 5,
            'items'    => array(
                'customcrm.crm' => array(
                    'id'        => 'menu_crm_parent',
                    'iconClass' => 'fa-check-square',
                    'children'  => array(
                        'customcrm.opportunities' => array(
                            'route' => 'mautic_customcrm_opportunity_index'
                        ),
                        'ddi.lead_actions.tasks.list' => array(
                            'route' => 'ddi_lead_actions_task_index'
                        ),
                    )
                )
            )
        )
    )
);
