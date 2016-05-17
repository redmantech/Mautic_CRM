<?php

namespace MauticPlugin\CustomCrmBundle\Controller;

use Mautic\CoreBundle\Helper\Chart\LineChart;
use Mautic\LeadBundle\Controller\LeadController as BaseLeadController;
use Mautic\LeadBundle\Event\LeadTimelineEvent;
use Mautic\LeadBundle\LeadEvents;

class LeadController extends BaseLeadController
{
    /**
     * Loads a specific lead into the detailed panel
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($objectId)
    {
        /** @var \Mautic\LeadBundle\Model\LeadModel $model */
        $model = $this->factory->getModel('lead.lead');

        /** @var \Mautic\LeadBundle\Entity\Lead $lead */
        $lead = $model->getEntity($objectId);

        //set the page we came from
        $page = $this->factory->getSession()->get('mautic.lead.page', 1);

        //set some permissions
        $permissions = $this->factory->getSecurity()->isGranted(
            array(
                'lead:leads:viewown',
                'lead:leads:viewother',
                'lead:leads:create',
                'lead:leads:editown',
                'lead:leads:editother',
                'lead:leads:deleteown',
                'lead:leads:deleteother'
            ),
            "RETURN_ARRAY"
        );

        if ($lead === null) {
            //set the return URL
            $returnUrl = $this->generateUrl('mautic_lead_index', array('page' => $page));

            return $this->postActionRedirect(
                array(
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => array('page' => $page),
                    'contentTemplate' => 'MauticLeadBundle:Lead:index',
                    'passthroughVars' => array(
                        'activeLink'    => '#mautic_lead_index',
                        'mauticContent' => 'lead'
                    ),
                    'flashes'         => array(
                        array(
                            'type'    => 'error',
                            'msg'     => 'mautic.lead.lead.error.notfound',
                            'msgVars' => array('%id%' => $objectId)
                        )
                    )
                )
            );
        }

        if (!$this->factory->getSecurity()->hasEntityAccess(
            'lead:leads:viewown',
            'lead:leads:viewother',
            $lead->getOwner()
        )
        ) {
            return $this->accessDenied();
        }

        $filters = $this->factory->getSession()->get(
            'mautic.lead.'.$lead->getId().'.timeline.filters',
            array(
                'search'        => '',
                'includeEvents' => array(),
                'excludeEvents' => array()
            )
        );

        // Get Places from IP addresses
        $places = array();
        if ($lead->getIpAddresses()) {
            foreach ($lead->getIpAddresses() as $ip) {
                if ($details = $ip->getIpDetails()) {
                    if (!empty($details['latitude']) && !empty($details['longitude'])) {
                        $name = 'N/A';
                        if (!empty($details['city'])) {
                            $name = $details['city'];
                        } elseif (!empty($details['region'])) {
                            $name = $details['region'];
                        }
                        $place = array(
                            'latLng' => array($details['latitude'], $details['longitude']),
                            'name' => $name,
                        );
                        $places[] = $place;
                    }
                }
            }
        }

        // Trigger the TIMELINE_ON_GENERATE event to fetch the timeline events from subscribed bundles
        $dispatcher = $this->factory->getDispatcher();
        $event      = new LeadTimelineEvent($lead, $filters);
        $dispatcher->dispatch(LeadEvents::TIMELINE_ON_GENERATE, $event);

        $eventsByDate = $event->getEvents(true);
        $eventTypes   = $event->getEventTypes();

        // Get an engagement count
        $translator     = $this->factory->getTranslator();

        $fromDate       = (new \DateTime('first day of this month 00:00:00'))->modify('-6 months');
        $toDate         = new \DateTime;
        $engagements = array();
        $total          = 0;

        $events = array();
        foreach ($eventsByDate as $eventDate => $dateEvents) {
            $datetime = \DateTime::createFromFormat('Y-m-d H:i', $eventDate);
            if ($datetime > $fromDate) {
                $total++;
                $engagements[] = array(
                    'date' => $eventDate,
                    'data' => 1
                );
            }
            $events = array_merge($events, array_reverse($dateEvents));
        }

        $lineChart   = new LineChart(null, $fromDate, $toDate);
        $query       = $lineChart->getChartQuery($this->factory->getEntityManager()->getConnection());
        $engagements = $query->completeTimeData($engagements);
        $pointStats  = $query->fetchTimeData('lead_points_change_log', 'date_added', array('lead_id' => $lead->getId()));
        $lineChart->setDataset($translator->trans('mautic.lead.graph.line.all_engagements'), $engagements);
        $lineChart->setDataset($translator->trans('mautic.lead.graph.line.points'), $pointStats);
        $engagementChart = $lineChart->render();

        // Upcoming events from Campaign Bundle
        /** @var \Mautic\CampaignBundle\Entity\LeadEventLogRepository $leadEventLogRepository */
        $leadEventLogRepository = $this->factory->getEntityManager()->getRepository('MauticCampaignBundle:LeadEventLog');

        $upcomingEvents = $leadEventLogRepository->getUpcomingEvents(array('lead' => $lead, 'scheduled' => 1, 'eventType' => 'action'));

        $fields            = $lead->getFields();
        $integrationHelper = $this->factory->getHelper('integration');
        $socialProfiles    = $integrationHelper->getUserProfiles($lead, $fields);
        $socialProfileUrls = $integrationHelper->getSocialProfileUrlRegex(false);

        // Set the social profile templates
        foreach ($socialProfiles as $integration => &$details) {
            if ($integrationObject = $integrationHelper->getIntegrationObject($integration)) {
                if ($template = $integrationObject->getSocialProfileTemplate()) {
                    $details['social_profile_template'] = $template;
                }
            }

            if (!isset($details['social_profile_template'])) {
                // No profile template found
                unset($socialProfiles[$integration]);
            }
        }

        // We need the EmailRepository to check if a lead is flagged as do not contact
        /** @var \Mautic\EmailBundle\Entity\EmailRepository $emailRepo */
        $emailRepo = $this->factory->getModel('email')->getRepository();

        // Get tasks count
        $repository = $this->getDoctrine()->getRepository('CustomCrmBundle:Task');
        // Get completed tasks
        /** @var \Doctrine\ORM\QueryBuilder $query */
        $query = $repository->createQueryBuilder('t');
        $query
            ->select('COUNT(t)')
            ->where('t.lead = :id')
            ->andWhere('t.isCompleted = 0')
            ->setParameter('id', $objectId);
        $tasksCount = $query->getQuery()->getSingleScalarResult();

        return $this->delegateView(
            array(
                'viewParameters'  => array(
                    'lead'              => $lead,
                    'avatarPanelState'  => $this->request->cookies->get('mautic_lead_avatar_panel', 'expanded'),
                    'fields'            => $fields,
                    'socialProfiles'    => $socialProfiles,
                    'socialProfileUrls' => $socialProfileUrls,
                    'places'            => $places,
                    'security'          => $this->factory->getSecurity(),
                    'permissions'       => $permissions,
                    'events'            => $events,
                    'eventTypes'        => $eventTypes,
                    'eventFilters'      => $filters,
                    'upcomingEvents'    => $upcomingEvents,
                    'engagementData'    => $engagementChart,
                    'noteCount'         => $this->factory->getModel('lead.note')->getNoteCount($lead, true),
                    'doNotContact'      => $emailRepo->checkDoNotEmail($fields['core']['email']['value']),
                    'leadNotes'         => $this->forward(
                        'MauticLeadBundle:Note:index',
                        array(
                            'leadId'     => $lead->getId(),
                            'ignoreAjax' => 1
                        )
                    )->getContent(),

                    // opportunities
                    'opportunities'      => $this->factory->getModel('plugin.customCrm.opportunity')->getRepository()
                        ->findByLead($lead),

                    // tasks
                    'tasksCount' => $tasksCount,
                    'leadTasks' => $this->forward(
                        'CustomCrmBundle:Task:tab',
                        array(
                            'leadId'     => $lead->getId(),
                            'ignoreAjax' => 1
                        )
                    )->getContent(),
                ),
                'contentTemplate' => 'MauticLeadBundle:Lead:lead.html.php',
                'passthroughVars' => array(
                    'activeLink'    => '#mautic_lead_index',
                    'mauticContent' => 'lead',
                    'route'         => $this->generateUrl(
                        'mautic_lead_action',
                        array(
                            'objectAction' => 'view',
                            'objectId'     => $lead->getId()
                        )
                    )
                )
            )
        );
    }
}