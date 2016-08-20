<?php

namespace MauticPlugin\CustomCrmBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use MauticPlugin\CustomCrmBundle\Entity\Task;
use Symfony\Component\HttpFoundation\JsonResponse;

class TaskController extends FormController
{
    public function indexAction($page = 1)
    {
        $tmpl = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';

        $session = $this->factory->getSession();
        $search = $this->request->get('search', $session->get('ddi.lead_actions.task.filter', ''));
        $session->set('ddi.lead_actions.task.filter', $search);

        $dueDateFilter = $this->request->get('filter', $session->get('ddi.lead_actions.task.filter.due_date', null));
        $session->set('ddi.lead_actions.task.filter.due_date', $dueDateFilter);

        $repository = $this->getDoctrine()->getRepository('CustomCrmBundle:Task');

        $emConfig = $this->getDoctrine()->getEntityManager()->getConfiguration();
        $emConfig->addCustomDatetimeFunction('DATE', 'DoctrineExtensions\Query\Mysql\Date');

        /** @var \Doctrine\ORM\QueryBuilder $query */
        $query = $repository->createQueryBuilder('t');
        $query
            ->where('t.name LIKE :name')
            ->setParameter('name', '%' . $search . '%');

        // Filter by date
        if ($dueDateFilter) {
            $date = new \DateTime($dueDateFilter);
            $query
                ->andWhere('DATE(t.dueDate) = :dueDate')
                ->setParameter('dueDate', $date->format('Y-m-d'));
        }

        $owner = $this->request->get('owner', $session->get('ddi.lead_actions.task.owner', ''));
        $session->set('ddi.lead_actions.task.owner', $owner);

        // Filter by owner
        $ownerMeFilter = false;
        if ($owner == 'me') {
            $ownerMeFilter = true;
            $query
                ->andWhere('t.assignUser = :id')
                ->setParameter('id', $this->getUser()->getId());
        }
        // Uncompleted, order by added
        $query
            ->andWhere('t.isCompleted = 0')
            ->orderBy('t.id', 'DESC');

        $tasks = $query->getQuery()->getResult();

        // Connect leads to tasks
        $model = $this->factory->getModel('lead.lead');
        $leads = $model->getEntities();
        foreach ($tasks as $task) {
            foreach ($leads as $lead) {
                if ($task->getLead()->getId() == $lead->getId()) {
                    $task->setLead($lead);
                }
            }
        }

        $this->factory->getSession()->set('ddi.lead_actions.task.page', $page);

        return $this->delegateView(
            array(
                'viewParameters' => array(
                    'searchValue' => $search,
                    'dueDateFilter' => $dueDateFilter,
                    'tmpl' => $tmpl,
                    'page' => $page,
                    'items' => $tasks,
                    'ownerMe' => $ownerMeFilter
                ),
                'contentTemplate' => 'CustomCrmBundle:Task:list.html.php',
                'passthroughVars' => array(
                    'route'         => $this->generateUrl('ddi_lead_actions_task_index')
                )
            )
        );
    }

    public function batchDeleteAction()
    {
        $page = $this->factory->getSession()->get('mautic.task.page', 1);
        $returnUrl = $this->generateUrl('ddi_lead_actions_task_index', array('page' => $page));
        $flashes = array();

        $postActionVars = array(
            'returnUrl'       => $returnUrl,
            'viewParameters'  => array('page' => $page),
            'contentTemplate' => 'CustomCrmBundle:Task:index',
            'passthroughVars' => array(
                'activeLink'    => '#mautic_task_index',
                'mauticContent' => 'task'
            )
        );

        if ($this->request->getMethod() == 'POST') {
            $ids = json_decode($this->request->query->get('ids', '{}'));
            foreach ($ids as $objectId) {
                $task = $this->getDoctrine()->getRepository('CustomCrmBundle:Task')->findOneById($objectId);

                $em = $this->getDoctrine()->getEntityManager();
                $em->remove($task);
                $em->flush();
            }
        }

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                array(
                    'flashes' => $flashes
                )
            )
        );
    }

    public function deleteAction($objectId)
    {
        $page = $this->factory->getSession()->get('mautic.task.page', 1);
        $returnUrl = $this->generateUrl('ddi_lead_actions_task_index', array('page' => $page));
        $flashes = array();

        $postActionVars = array(
            'returnUrl'       => $returnUrl,
            'viewParameters'  => array('page' => $page),
            'contentTemplate' => 'CustomCrmBundle:Task:index',
            'passthroughVars' => array(
                'activeLink'    => '#mautic_task_index',
                'mauticContent' => 'task'
            )
        );

        if ($this->request->getMethod() == 'POST') {
            $task = $this->getDoctrine()->getRepository('CustomCrmBundle:Task')->findOneById($objectId);

            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($task);
            $em->flush();
        }

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                array(
                    'flashes' => $flashes
                )
            )
        );
    }

    public function executeTaskAction($objectAction, $objectId = 0)
    {
        if (method_exists($this, "{$objectAction}Action")) {
            return $this->{"{$objectAction}Action"}($objectId);
        } else {
            return $this->accessDenied();
        }
    }

    public function newAction($leadId)
    {
        $task = new Task();
        $model  = $this->factory->getModel('customcrm.task');
        $action = $this->generateUrl('mautic_task_action', array(
                'objectAction' => 'new',
                'objectId'       => $leadId)
        );
        $form = $model->createForm($task, $this->get('form.factory'), $action);
        $valid      = false;
        $closeModal = false;
        if ($this->request->getMethod() == 'POST') {
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $closeModal = true;

                    $lead = $this->getDoctrine()->getRepository('MauticLeadBundle:Lead')->findOneById($leadId);
                    $task->setLead($lead);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($task);
                    $em->flush();
                }
            } else {
                $closeModal = true;
            }
        }

        if ($closeModal) {
            $passthroughVars = array(
                'closeModal'    => 1,
                'mauticContent' => 'task'
            );

            if ($valid && !$cancelled) {
                $passthroughVars['upTaskCount'] = 1;
                $passthroughVars['html'] = $this->renderView('CustomCrmBundle:Task:task.html.php', array(
                    'task' => $task
                ));
                $passthroughVars['taskId']   = $task->getId();
            }

            $response = new JsonResponse($passthroughVars);
            $response->headers->set('Content-Length', strlen($response->getContent()));

            return $response;
        } else {
            return $this->delegateView(array(
                'viewParameters'  => array(
                    'form'        => $form->createView(),
                ),
                'contentTemplate' => 'CustomCrmBundle:Task:form.html.php'
            ));
        }
    }

    public function completedAction($objectId)
    {
        $task = $this->getDoctrine()->getRepository('CustomCrmBundle:Task')->findOneById($objectId);
        $task->setIsCompleted(1);
        $task->setDateCompleted(new \DateTime('now'));
        $this->getDoctrine()->getManager()->flush();

        $page = $this->factory->getSession()->get('mautic.task.page', 1);
        $returnUrl = $this->generateUrl('ddi_lead_actions_task_index', array('page' => $page));
        return $this->postActionRedirect(
            array(
                'returnUrl' => $returnUrl,
                'viewParameters' => array('page' => $page),
                'contentTemplate' => 'CustomCrmBundle:Task:index',
            )
        );
    }

    /**
     * Tab on lead page
     *
     * @param $leadId
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function tabAction($leadId)
    {
        $repository = $this->getDoctrine()->getRepository('CustomCrmBundle:Task');
        // Get completed tasks
        /** @var \Doctrine\ORM\QueryBuilder $query */
        $query = $repository->createQueryBuilder('t');
        $query
            ->where('t.lead = :id')
            ->andWhere('t.isCompleted = 0')
            ->orderBy('t.id', 'DESC')
            ->setParameter('id', $leadId);
        $tasks = $query->getQuery()->getResult();

        return $this->delegateView(array(
            'viewParameters'  => array(
                'tasks'       => $tasks,
            ),
            'contentTemplate' => 'CustomCrmBundle:Task:tab.html.php'
        ));
    }

    public function editAction($objectId)
    {
        $session = $this->factory->getSession();
        $type = $this->request->get('type', $session->get('ddi.lead_actions.task.form.type', ''));
        $session->set('ddi.lead_actions.task.form.type', $type);

        $model  = $this->factory->getModel('customcrm.task');
        $task = $model->getEntity($objectId);
        $action = $this->generateUrl('mautic_task_action', array(
                'objectAction' => 'edit',
                'objectId'      => $objectId)
        );
        $form = $model->createForm($task, $this->get('form.factory'), $action);
        $valid      = false;
        $closeModal = false;
        if ($this->request->getMethod() == 'POST') {
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $closeModal = true;

                    // Save task
                    $em = $this->getDoctrine()->getManager();
                    $em->flush();
                }
            } else {
                $closeModal = true;
            }
        }

        if ($closeModal) {
            $passthroughVars = array(
                'closeModal'    => 1,
                'mauticContent' => 'task'
            );

            if ($valid && !$cancelled) {
                $passthroughVars['upTaskCount'] = 0;
                $passthroughVars['html'] = $this->renderView('CustomCrmBundle:Task:task.html.php', array(
                    'task' => $task
                ));
                $passthroughVars['taskId']   = $task->getId();
            }

            if ($type) {

                $response = new JsonResponse($passthroughVars);
                $response->headers->set('Content-Length', strlen($response->getContent()));
                return $response;

            } else {
                $page = $this->factory->getSession()->get('mautic.task.page', 1);
                $returnUrl = $this->generateUrl('ddi_lead_actions_task_index', array('page' => $page));
                return $this->postActionRedirect(
                    array(
                        'returnUrl' => $returnUrl,
                        'viewParameters' => array('page' => $page),
                        'contentTemplate' => 'CustomCrmBundle:Task:index',
                        'passthroughVars' => $passthroughVars
                    )
                );
            }
        } else {
            return $this->delegateView(array(
                'viewParameters'  => array(
                    'form'        => $form->createView(),
                ),
                'contentTemplate' => 'CustomCrmBundle:Task:form.html.php'
            ));
        }
    }
}