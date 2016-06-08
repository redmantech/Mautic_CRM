<?php

namespace MauticPlugin\CustomCrmBundle\Command;

use Mautic\CoreBundle\Command\ModeratedCommand;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\CustomCrmBundle\Entity\Task;
use MauticPlugin\CustomCrmBundle\Model\TaskModel;
use MauticPlugin\CustomCrmBundle\Repository\TaskRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TaskNotifierCommand extends ModeratedCommand
{
    protected function configure()
    {
        $this
            ->setName('mautic:customcrm:tasks:notify')
            ->setDescription('Send notifications with today\'s tasks.')
        ;

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container  = $this->getContainer();
        /** @var MauticFactory $factory */
        $factory    = $container->get('mautic.factory');
        /** @var LeadModel $model */
        $leadModel = $factory->getModel('lead.lead');
        /** @var TaskModel $taskModel */
        $taskModel  = $factory->getModel('plugin.customCrm.task');
        /** @var TaskRepository $repo */
        $repo       = $taskModel->getRepository();
        /** @var Task[] $tasks */
        $tasks      = $repo->getOpenTasks(new \DateTime());
        $grouped    = array();
        $users      = array();

        foreach ($tasks as $key => $task) {
            var_dump($task->getId());
            $users[$task->getAssignUser()->getId()] = $task->getAssignUser();
            $grouped[$task->getAssignUser()->getId()][] = $task;
            $fields =  $leadModel->getRepository()->getFieldValues($task->getLead()->getId());
            $task->getLead()->setFields($fields);
            unset($tasks[$key]);
        }

        foreach ($grouped as $id => $tasks)
        {
            $user = $users[$id];
            $taskCount = count($tasks);
            if ($taskCount == 1) {
                $subject = 'One task is due today';
            } else {
                $subject = sprintf(
                    '%s tasks are due today',
                    $taskCount
                );
            }

            $body = $factory->getTemplating()->render(
                'CustomCrmBundle:Task:emails/openTasks.html.php',
                array(
                    'tasks' => $tasks,
                    'user' => $user,
                )
            );

            $mailer = $factory->getMailer();
            $mailer->setBody($body);
            $mailer->setTo($user->getEmail(), $user->getName());
            $mailer->setSubject($subject);
            $mailer->send();
            foreach ($tasks as $task) {
                $task->setNotified(true);
            }
            $factory->getEntityManager()->flush($tasks);
        }
    }
}