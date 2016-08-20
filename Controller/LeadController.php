<?php

namespace MauticPlugin\CustomCrmBundle\Controller;

use Mautic\LeadBundle\Controller\LeadController as BaseLeadController;

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
        $model = $this->getModel('lead.lead');

        /** @var \Mautic\LeadBundle\Entity\Lead $lead */
        $lead = $model->getEntity($objectId);

        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'lead:leads:viewown',
                'lead:leads:viewother',
                'lead:leads:create',
                'lead:leads:editown',
                'lead:leads:editother',
                'lead:leads:deleteown',
                'lead:leads:deleteother'
            ],
            "RETURN_ARRAY"
        );

        if ($lead === null) {
            //get the page we came from
            $page = $this->get('session')->get('mautic.lead.page', 1);

            //set the return URL
            $returnUrl = $this->generateUrl('mautic_contact_index', ['page' => $page]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page],
                    'contentTemplate' => 'MauticLeadBundle:Lead:index',
                    'passthroughVars' => [
                        'activeLink'    => '#mautic_contact_index',
                        'mauticContent' => 'contact'
                    ],
                    'flashes'         => [
                        [
                            'type'    => 'error',
                            'msg'     => 'mautic.lead.lead.error.notfound',
                            'msgVars' => ['%id%' => $objectId]
                        ]
                    ]
                ]
            );
        }

        if (!$this->get('mautic.security')->hasEntityAccess(
            'lead:leads:viewown',
            'lead:leads:viewother',
            $lead->getOwner()
        )
        ) {
            return $this->accessDenied();
        }

        $fields            = $lead->getFields();
        $integrationHelper = $this->get('mautic.helper.integration');
        $socialProfiles    = (array) $integrationHelper->getUserProfiles($lead, $fields);
        $socialProfileUrls = $integrationHelper->getSocialProfileUrlRegex(false);

        // Set the social profile templates
        if ($socialProfiles) {
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
        }

        // We need the EmailRepository to check if a lead is flagged as do not contact
        /** @var \Mautic\EmailBundle\Entity\EmailRepository $emailRepo */
        $emailRepo = $this->getModel('email')->getRepository();

        return $this->delegateView(
            [
                'viewParameters'  => [
                    'lead'              => $lead,
                    'avatarPanelState'  => $this->request->cookies->get('mautic_lead_avatar_panel', 'expanded'),
                    'fields'            => $fields,
                    'socialProfiles'    => $socialProfiles,
                    'socialProfileUrls' => $socialProfileUrls,
                    'places'            => $this->getPlaces($lead),
                    'permissions'       => $permissions,
                    'events'            => $this->getEngagements($lead),
                    'upcomingEvents'    => $this->getScheduledCampaignEvents($lead),
                    'engagementData'    => $this->getEngagementData($lead),
                    'noteCount'         => $this->getModel('lead.note')->getNoteCount($lead, true),
                    'doNotContact'      => $emailRepo->checkDoNotEmail($fields['core']['email']['value']),
                    'leadNotes'         => $this->forward(
                        'MauticLeadBundle:Note:index',
                        [
                            'leadId'     => $lead->getId(),
                            'ignoreAjax' => 1
                        ]
                    )->getContent(),
                    // opportunities
                    'opportunities'      => $this->factory->getModel('customcrm.opportunity')->getRepository()
                        ->findByLead($lead),

                    // tasks
                    'tasksCount' => $this->getModel('customcrm.task')->getRepository()->getCompletedCount($objectId),
                    'leadTasks' => $this->forward(
                        'CustomCrmBundle:Task:tab',
                        array(
                            'leadId'     => $lead->getId(),
                            'ignoreAjax' => 1
                        )
                    )->getContent(),

                ],
                'contentTemplate' => 'MauticLeadBundle:Lead:lead.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_contact_index',
                    'mauticContent' => 'lead',
                    'route'         => $this->generateUrl(
                        'mautic_contact_action',
                        [
                            'objectAction' => 'view',
                            'objectId'     => $lead->getId()
                        ]
                    )
                ]
            ]
        );
    }
}