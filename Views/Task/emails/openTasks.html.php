<html>
<body>
    <h3>Hey <?php echo $user->getName() ?>,</h3>

    The following tasks are assigned to you and due this week:

    <ul>
    <?php foreach ($tasks as $task): ?>
        <li>
            <?php $url = $view['router']->generate(
                    'mautic_task_action',
                    array(
                        'objectId' => $task->getId(),
                        'objectAction' => 'view'
                    ),
                    true
                )
            ?>

            <?php $leadUrl = $view['router']->generate(
                'mautic_lead_action',
                array(
                    'objectId' => $task->getId(),
                    'objectAction' => 'view'
                ),
                true
            )
            ?>

            <a href="<?php echo $url ?>"><?php echo trim($task->getName()) ?></a>

            created on <?php echo $task->getDateAdded()->format('m/d/Y') ?>
            for <a href="<?php echo $leadUrl ?>">
                <?php echo $task->getLead()->getName() ?>
            </a>

        </li>
    <?php endforeach ?>
    </ul>
</body>
</html>