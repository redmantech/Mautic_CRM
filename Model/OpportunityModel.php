<?php

namespace MauticPlugin\CustomCrmBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\CustomCrmBundle\Entity\Opportunity;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class OpportunityModel extends FormModel
{
    public function getRepository()
    {
        return $this->em->getRepository('CustomCrmBundle:Opportunity');
    }

    public function getPermissionBase()
    {
        return 'customcrm:opportunity';
    }

    public function getEntity($id = null)
    {
        if (!$id) {
            return new Opportunity();
        }

        return parent::getEntity($id);
    }

    public function createForm($entity, $formFactory, $action = null, $options = array())
    {
        if (!$entity instanceof Opportunity) {
            throw new MethodNotAllowedHttpException(array('Opportunity'), 'Entity must be of class Opportunity()');
        }

        if (!empty($action))  {
            $options['action'] = $action;
        }

        return $formFactory->create('customcrm_opportunity', $entity, $options);
    }

    public function saveEntity($entity, $unlock = true)
    {
        if ($entity->getId()) {
            $this->em->flush();
            return;
        }

        if (!$entity instanceof Opportunity) {
            throw new \InvalidArgumentException('entity is not an instance of Opportunity');
        }

        if (!$entity->getOwnerUser()) {
            $entity->setOwnerUser($this->factory->getUser(true));
        }

        if ($leadId = $this->factory->getRequest()->get('leadId', false)) {
            $entity->setLead($this->factory->getModel('lead')->getEntity($leadId));
        }

        parent::saveEntity($entity);
    }

    public function getStatusList()
    {
        $result = $this->getRepository()->createQueryBuilder('o')
            ->select('DISTINCT o.status as id')
            ->getQuery()->getArrayResult();

        foreach ($result as &$row) {
            $row['name'] = $this->factory->getTranslator()->trans(Opportunity::getStatusLabels($row['id']));
        }

        return $result;
    }

}
