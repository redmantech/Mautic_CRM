<?php

if ($tmpl == 'index')
    $view->extend('CustomCrmBundle:Task:index.html.php');
?>

<?php if (count($items)) { ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered" id="taskTable">
            <thead>
            <tr>
                <?php

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                    'checkall' => 'true',
                    'target'   => '#taskTable'
                ));

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                    'sessionVar' => 'task',
                    'orderBy'    => 'l.lastname, l.firstname, l.company, l.email',
                    'text'       => 'ddi.lead_actions.tasks.thead.completed',
                    'class'      => 'col-task-completed'
                ));

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                    'sessionVar' => 'task',
                    'orderBy'    => 'l.city, l.state',
                    'text'       => 'ddi.lead_actions.tasks.thead.name',
                    'class'      => 'col-task-name_of_the_task visible-md visible-lg'
                ));

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                    'sessionVar' => 'task',
                    'orderBy'    => 'l.date',
                    'text'       => 'ddi.lead_actions.tasks.thead.due_date',
                    'class'      => 'col-task-due_date visible-md visible-lg'
                ));

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                    'sessionVar' => 'task',
                    'orderBy'    => 'l.city, l.state',
                    'text'       => 'ddi.lead_actions.tasks.thead.lead',
                    'class'      => 'col-task-lead visible-md visible-lg'
                ));

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                    'sessionVar' => 'task',
                    'orderBy'    => 'l.city, l.state',
                    'text'       => 'ddi.lead_actions.tasks.thead.assigned_user',
                    'class'      => 'col-task-assignuser visible-md visible-lg'
                ));

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                    'sessionVar' => 'task',
                    'orderBy'    => 'l.id',
                    'text'       => 'mautic.core.id',
                    'class'      => 'col-task-id visible-md visible-lg',
                    'default'    => true
                ));
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item) { ?>
                <tr>
                    <td>
                        <?php

                        $custom = array();
                        // Make completed task
                        $custom[] = array(
                            'attr' => array(
                                'href' => $view['router']->generate('mautic_task_action',
                                    array(
                                        'objectAction' => 'completed',
                                        'objectId' => $item->getId()
                                    )
                                ),
                            ),
                            'btnText' => 'ddi.lead_actions.tasks.make_completed',
                            'iconClass'  => 'fa fa-check'
                        );

                        echo $view->render('MauticCoreBundle:Helper:list_actions.html.php', array(
                            'item'      => $item,
                            'editMode'  => 'ajaxmodal',
                            'editAttr'  => 'data-target="#MauticSharedModal" data-header="Edit task"',
                            'templateButtons' => array(
                                'edit'      => true,
                                'delete'    => true
                            ),
                            'routeBase' => 'task',
                            'langVar'   => 'task.task',
                            'customButtons' => $custom
                        ));
                        ?>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $item->getIsCompleted() ? 'Completed' : 'Uncompleted'; ?></td>
                    <td class="visible-md visible-lg">
                        <?php
                        $str = $item->getName();
                        $out = strlen($str) > 60 ? substr($str, 0, 60) . '...' : $str;
                        echo $out;
                        ?>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $item->getDueDate()->format('Y-m-d H:i'); ?></td>
                    <td class="visible-md visible-lg">
                        <a href="<?php echo $view['router']->generate('mautic_lead_action',
                            array('objectAction' => 'view',
                                'objectId' => $item->getLead()->getId()
                            )); ?>" data-toggle="ajax">
                            <div>
                                <?php echo $item->getLead()->getPrimaryIdentifier(); ?>
                            </div>
                            <div class="small"><?php echo $item->getLead()->getSecondaryIdentifier(); ?></div>
                        </a>
                    </td>
                    <td><?php echo $item->getAssignUser()->getFirstName() . ' ' . $item->getAssignUser()->getLastName(); ?></td>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <div class="panel-footer">
        <?php /*echo $view->render('MauticCoreBundle:Helper:pagination.html.php', array(
            "totalItems"      => $totalItems,
            "page"            => $page,
            "limit"           => $limit,
            "menuLinkId"      => 'mautic_lead_index',
            "baseUrl"         => $view['router']->generate('mautic_lead_index'),
            "tmpl"            => $indexMode,
            'sessionVar'      => 'lead'
        ));*/ ?>
    </div>
<?php } else { ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
<?php } ?>