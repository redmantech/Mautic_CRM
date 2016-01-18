<?php

namespace MauticPlugin\CustomCrmBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\CustomCrmBundle\Entity\Task;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class TaskModel extends FormModel
{
    public function getRepository()
    {
        return $this->em->getRepository('CustomCrmBundle:Task');
    }

    public function createForm($entity, $formFactory, $action = null, $options = array())
    {
        if (!$entity instanceof Task) {
            throw new MethodNotAllowedHttpException(array('Task'));
        }
        $params = (!empty($action)) ? array('action' => $action) : array();
        return $formFactory->create('task', $entity, $params);
    }
}