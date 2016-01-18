<?php

namespace MauticPlugin\CustomCrmBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use MauticPlugin\CustomCrmBundle\Entity\Opportunity;
use Symfony\Component\HttpFoundation\JsonResponse;

class OpportunityController extends FormController
{
    public function indexAction($page = 1)
    {
        /** @var \MauticPlugin\CustomCrmBundle\Model\OpportunityModel $model */
        $model = $this->factory->getModel('plugin.customCrm.opportunity');

        //set what page currently on so that we can return here after form submission/cancellation
        $this->factory->getSession()->set('customcrm.opportunity.page', $page);

        //set limits
        $limit = $this->factory->getSession()->get('customcrm.opportunity.limit', $this->factory->getParameter('default_pagelimit'));
        $start = ($page === 1) ? 0 : (($page-1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        if ($this->request->getMethod() == 'POST') {
            $this->setListFilters();
        }

        $listFilters = array(
            'filters'      => array(
                'multiple' => true
            ),
        );

        // Reset available groups
        $listFilters['filters']['groups'] = array();

        //retrieve a list of categories
        $listFilters['filters']['groups']['mautic.customcrm.opportunity.filter.user'] = array(
            'options'  => array_map(function($user) {
                return array('name' => $user['firstName'] . ' ' . $user['lastName'], 'id' => $user['id']);
            }, $this->factory->getModel('user')->getLookupResults('user', '', 0)),
            'prefix'   => 'user'
        );

        //retrieve a list of categories
        $listFilters['filters']['groups']['mautic.customcrm.opportunity.filter.status'] = array(
            'options'  => $model->getStatusList(),
            'prefix'   => 'status'
        );

        $session = $this->factory->getSession();
        $currentFilters = $session->get('mautic.customcrm.opportunity.list_filters', array());
        $updatedFilters = $this->request->get('filters', false);

        if ($updatedFilters) {
            // Filters have been updated

            // Parse the selected values
            $newFilters     = array();
            $updatedFilters = json_decode($updatedFilters, true);

            if ($updatedFilters) {
                foreach ($updatedFilters as $updatedFilter) {
                    list($clmn, $fltr) = explode(':', $updatedFilter);

                    $newFilters[$clmn][] = $fltr;
                }

                $currentFilters = $newFilters;
            } else {
                $currentFilters = array();
            }
        }
        $session->set('mautic.customcrm.opportunity.list_filters', $currentFilters);
        $filter = array();

        if (!empty($currentFilters)) {
            $catIds = $templates = array();
            foreach ($currentFilters as $type => $typeFilters) {
                switch ($type) {
                    case 'user':
                        $key = 'users';
                        break;
                    case 'status':
                        $key = 'status';
                        break;
                }

                $listFilters['filters']['groups']['mautic.core.filter.' . $key]['values'] = $typeFilters;

                foreach ($typeFilters as $fltr) {
                    switch ($type) {
                        case 'user':
                            $userIds[] = (int) $fltr;
                            break;
                        case 'status':
                            $statusIds[] = $fltr;
                            break;
                    }
                }
            }

            if (!empty($userIds)) {
                $filter['force'][] = array('column' => 'o.id', 'expr' => 'in', 'value' => $userIds);
            }

            if (!empty($statusIds)) {
                $filter['force'][] = array('column' => 'e.status', 'expr' => 'in', 'value' => $statusIds);
            }
        }

        $items = $model->getEntities(
            array(
                'start'      => $start,
                'limit'      => $limit,
                'filter'     => $filter
            ));

        $listIds    = array_keys($items->getIterator()->getArrayCopy());
        $opportunityCounts = (!empty($listIds)) ? $model->getRepository()->getOpportunityCount($listIds) : array();

        $leadModel = $this->factory->getModel('lead.lead');
        $leadModel->getEntities(array_map(function($item) {
            return $item->getLead()->getId();
        }, $items->getQuery()->getResult()));

        $items->getQuery()->getResult();

        $parameters = array(
            'items'       => $items,
            'opportunityCounts'  => $opportunityCounts,
            'page'        => $page,
            'limit'       => $limit,
            'tmpl'        => $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index',
            'currentUser' => $this->factory->getUser(),
            'filters'     => $listFilters
        );

        return $this->delegateView(array(
            'viewParameters'  => $parameters,
            'contentTemplate' => 'CustomCrmBundle:Opportunity:list.html.php',
            'passthroughVars' => array(
                'activeLink'     => '#mautic_customcrm_opportunity_index',
                'route'          => $this->generateUrl('mautic_customcrm_opportunity_index', array('page' => $page)),
                'mauticContent'  => 'opportunity'
            )
        ));
    }

    public function newAction()
    {
        /** @var \MauticPlugin\CustomCrmBundle\Model\OpportunityModel $model */
        $model = $this->factory->getModel('plugin.customCrm.opportunity');

        //set the page we came from
        $page       = $this->factory->getSession()->get('mautic.opportunity.page', 1);


        $returnUrl = $this->generateUrl('mautic_customcrm_opportunity_index', array('page' => $page));
        $action = $this->generateUrl('mautic_customcrm_opportunity_action', array(
            'objectAction' => 'new',
            'leadId' => $this->request->get('leadId'),
            'qf' => $this->request->get('qf')
        ));

        $opportunity = new Opportunity();
        $form = $model->createForm($opportunity, $this->get('form.factory'), $action, array(
            'isShortForm' => $this->request->get('qf', false)
        ));

        if ($this->request->isMethod('POST')) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $model->saveEntity($opportunity);

                    $this->addFlash('mautic.core.notice.created', array(
                        '%name%' => 'Opportunity #' . $opportunity->getId(),
                        '%menu_link%' => 'mautic_customcrm_opportunity_index',
                        '%url%' => $this->generateUrl('mautic_customcrm_opportunity_action', array(
                            'objectAction' => 'edit',
                            'objectId' => $opportunity->getId()
                        ))
                    ));
                }
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                // quick form from lead page
                if ($this->request->get('qf', false)) {
                    $passthroughVars = array(
                        'closeModal'    => 1,
                        'mauticContent' => 'opportunity'
                    );

                    if (!$cancelled) {
                        $passthroughVars['upOpportunityCount'] = 1;
                        $passthroughVars['opportunityHtml'] = $this->renderView('CustomCrmBundle:Opportunity:opportunity.html.php', array(
                            'opportunity'        => $opportunity,
                        ));
                        $passthroughVars['opportunityId']   = $opportunity->getId();
                        $passthroughVars['flashes'] = $this->getFlashContent();
                    }


                    $response = new JsonResponse($passthroughVars);
                    $response->headers->set('Content-Length', strlen($response->getContent()));

                    return $response;
                }

                return $this->postActionRedirect(array(
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => array('page' => $page),
                    'contentTemplate' => 'CustomCrmBundle:Opportunity:index',
                    'passthroughVars' => array(
                        'activeLink'    => '#mautic_customcrm_opportunity_index',
                        'mauticContent' => 'opportunity'
                    )
                ));
            } elseif ($valid && !$cancelled) {
                return $this->editAction($opportunity->getId());
            }
        }

        return $this->delegateView(array(
            'viewParameters' => array(
                'entity'          => $opportunity,
                'form'            => $this->setFormTheme($form, 'CustomCrmBundle:Opportunity:form.html.php')
            ),
            'contentTemplate' => 'CustomCrmBundle:Opportunity:'. ($this->request->get('qf', false) ? 'quickadd.html.php' : 'form.html.php'),
            'passthroughVars' => array(
                'activeLink'    => '#mautic_customcrm_opportunity_index',
                'route'         => $action,
                'mauticContent' => 'opportunity'
            )
        ));
    }

    public function editAction($objectId)
    {
        /** @var \MauticPlugin\CustomCrmBundle\Model\OpportunityModel $model */
        $model = $this->factory->getModel('plugin.customCrm.opportunity');

        //set the page we came from
        $page       = $this->factory->getSession()->get('mautic.opportunity.page', 1);
        //set the return URL for post actions
        $returnUrl  = $this->generateUrl('mautic_customcrm_opportunity_index', array('page' => $page));

        $postActionVars = array(
            'returnUrl'       => $returnUrl,
            'viewParameters'  => array('page' => $page),
            'contentTemplate' => 'CustomCrmBundle:Opportunity:index',
            'passthroughVars' => array(
                'activeLink'    => '#mautic_customcrm_opportunity_index',
                'mauticContent' => 'opportunity'
            )
        );

        $opportunity = $model->getEntity($objectId);
        if (!$opportunity) {
            throw $this->createNotFoundException();
        }

        $action = $this->generateUrl('mautic_customcrm_opportunity_action', array(
            'objectAction' => 'edit',
            'objectId' => $objectId,
            'qf' => $this->request->get('qf'),
            'leadId' => $this->request->get('leadId')
        ));

        $form = $model->createForm($opportunity, $this->get('form.factory'), $action, array(
            'isShortForm' => $this->request->get('qf', false)
        ));

        if ($this->request->isMethod('POST')) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $model->saveEntity($opportunity);

                    $this->addFlash('mautic.core.notice.updated', array(
                        '%name%' => 'Opportunity #' . $opportunity->getId(),
                        '%menu_link%' => 'mautic_customcrm_opportunity_index',
                        '%url%' => $this->generateUrl('mautic_customcrm_opportunity_action', array(
                            'objectAction' => 'edit',
                            'objectId' => $opportunity->getId()
                        ))
                    ));
                }
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {

                // quick form from lead page
                if ($this->request->get('qf', false)) {
                    $passthroughVars = array(
                        'closeModal'    => 1,
                        'mauticContent' => 'opportunity'
                    );

                    if (!$cancelled) {
                        $passthroughVars['opportunityHtml'] = $this->renderView('CustomCrmBundle:Opportunity:opportunity.html.php', array(
                            'opportunity'        => $opportunity,
                        ));
                        $passthroughVars['opportunityId']   = $opportunity->getId();
                        $passthroughVars['flashes'] = $this->getFlashContent();
                    }


                    $response = new JsonResponse($passthroughVars);
                    $response->headers->set('Content-Length', strlen($response->getContent()));

                    return $response;
                }

                return $this->postActionRedirect($postActionVars);
            }
        }

        return $this->delegateView(array(
            'viewParameters' => array(
                'entity'          => $opportunity,
                'form'            => $this->setFormTheme($form, 'CustomCrmBundle:Opportunity:form.html.php')
            ),
            'contentTemplate' => 'CustomCrmBundle:Opportunity:' . ($this->request->get('qf', false) ? 'quickadd.html.php' : 'form.html.php'),
            'passthroughVars' => array(
                'activeLink'    => '#mautic_customcrm_opportunity_index',
                'route'         => $action,
                'mauticContent' => 'opportunity'
            )
        ));
    }

    public function deleteAction($objectId)
    {
        $page      = $this->factory->getSession()->get('customcrm.opportunity.page', 1);
        $returnUrl = $this->generateUrl('mautic_customcrm_opportunity_index', array('page' => $page));

        $postActionVars = array(
            'returnUrl'       => $returnUrl,
            'viewParameters'  => array('page' => $page),
            'contentTemplate' => 'CustomCrmBundle:Opportunity:index',
            'passthroughVars' => array(
                'activeLink'    => '#mautic_customcrm_opportunity_index',
                'mauticContent' => 'opportunity'
            )
        );

        if ($this->request->getMethod() == 'POST') {
            /** @var \MauticPlugin\CustomCrmBundle\Model\OpportunityModel $model */
            $model  = $this->factory->getModel('plugin.customCrm.opportunity');
            $entity = $model->getEntity($objectId);

            if ($entity === null) {
                $this->addFlash('mautic.customcrm.opportunity.error.notfound', array('%id%' => $objectId), 'error');
            }

            $model->deleteEntity($entity);
            $this->addFlash('mautic.core.notice.deleted', array('%name%' => 'Opportunity #' . $objectId), 'notice');
        } //else don't do anything

        if ($this->request->get('qf', false)) {
            $passthroughVars = array(
                'closeModal'    => 1,
                'mauticContent' => 'opportunity',
                'upOpportunityCount' => -1
            );

            $passthroughVars['opportunityId'] = $objectId;
            $passthroughVars['deleted'] = 1;
            $passthroughVars['flashes'] = $this->getFlashContent();

            $response = new JsonResponse($passthroughVars);
            $response->headers->set('Content-Length', strlen($response->getContent()));

            return $response;
        }

        return $this->postActionRedirect($postActionVars);
    }

    public function batchDeleteAction()
    {
        $page        = $this->factory->getSession()->get('mautic.opportunity.page', 1);
        $returnUrl   = $this->generateUrl('mautic_customcrm_opportunity_index', array('page' => $page));
        $flashes     = array();

        $postActionVars = array(
            'returnUrl'       => $returnUrl,
            'viewParameters'  => array('page' => $page),
            'contentTemplate' => 'CustomCrmBundle:Opportunity:index',
            'passthroughVars' => array(
                'activeLink'    => '#mautic_customcrm_opportunity_index',
                'mauticContent' => 'opportunity'
            )
        );

        if ($this->request->getMethod() == 'POST') {
            $model     = $this->factory->getModel('plugin.customCrm.opportunity');
            $ids       = json_decode($this->request->query->get('ids', '{}'));
            $deleteIds = array();

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if ($entity === null) {
                    $flashes[] = array(
                        'type'    => 'error',
                        'msg'     => 'mautic.customcrm.opportunity.list.error.notfound',
                        'msgVars' => array('%id%' => $objectId)
                    );
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = array(
                    'type' => 'notice',
                    'msg'  => '%count% opportunities deleted',
                    'msgVars' => array(
                        '%count%' => count($entities)
                    )
                );
            }
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge($postActionVars, array(
                'flashes' => $flashes
            ))
        );
    }

    public function quickAddAction($objectId)
    {
        /** @var \MauticPlugin\CustomCrmBundle\Model\OpportunityModel $model */
        $model = $this->factory->getModel('plugin.customCrm.opportunity');

        if ($objectId) {
            // Get the quick add form
            $action = $this->generateUrl('mautic_customcrm_opportunity_action', array(
                'objectAction' => 'edit',
                'objectId'     => $objectId,
                'leadId'       => $this->request->get('leadId', null),
                'qf'           => 1
            ));
        } else {
            // Get the quick add form
            $action = $this->generateUrl('mautic_customcrm_opportunity_action', array(
                'objectAction' => 'new',
                'leadId'       => $this->request->get('leadId', null),
                'qf'           => 1
            ));
        }

        $quickForm = $model->createForm($model->getEntity($objectId), $this->get('form.factory'), $action, array(
            'isShortForm' => true
        ));

        return $this->delegateView(
            array(
                'viewParameters'  => array(
                    'form' => $quickForm->createView()
                ),
                'contentTemplate' => "CustomCrmBundle:Opportunity:quickadd.html.php",
                'passthroughVars' => array(
                    'activeLink'    => '#mautic_customcrm_opportunity_index',
                    'mauticContent' => 'opportunity',
                    'route'         => false
                )
            )
        );
    }

}