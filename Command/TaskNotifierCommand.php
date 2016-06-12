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
        if (!$this->checkRunStatus($input, $output, 'all')) {

            return 0;
        }

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
        $taskCount = count($tasks);
        $grouped    = array();
        $users      = array();

        $output->writeln("<info>Tasks to process: $taskCount</info>");

        foreach ($tasks as $key => $task) {
            $users[$task->getAssignUser()->getId()] = $task->getAssignUser();
            $grouped[$task->getAssignUser()->getId()][] = $task;
            $fields =  $leadModel->getRepository()->getFieldValues($task->getLead()->getId());
            $task->getLead()->setFields($fields);
            unset($tasks[$key]);
        }

        if ($taskCount) {
            $output->writeln("<comment>Sending emails ...</comment>");
        }

        foreach ($grouped as $id => $tasks)
        {
            $user = $users[$id];
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

            if ($taskCount) {
                $output->writeln(sprintf(
                    "<info>Emails sent: %s</info>",
                    count($grouped)
                    )
                );
            }
        }

        $this->completeRun();

        return 0;
    }
}