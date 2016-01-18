<li id="Task<?php echo $task->getId(); ?>">
    <div class="panel">
        <div class="panel-body box-layout">
            <div class="col-xs-11 col-sm-11">
                <p><?php echo $task->getName(); ?></p>
                <div class="mt-15 text-muted">
                    <i class="fa fa-clock-o fa-fw"></i><span class="small"><?php echo $task->getDueDate()->format('d/m/Y h:ia'); ?></span>
                </div>
            </div>
            <div class="buttons col-xs-1 col-sm-2">
                <a class="btn btn-default btn-sm btn-nospin" data-target="#MauticSharedModal" data-toggle="ajaxmodal" data-header="Edit task" href="<?php echo $view['router']->generate('mautic_task_action', array('objectAction' => 'edit', 'objectId' => $task->getId())); ?>?type=tab"><span><i class="fa fa-clock-o"></i> </span></a>
                <a onclick="taskMakeCompleted(this, '<?php echo $view['router']->generate('mautic_task_action', array('objectAction' => 'completed', 'objectId' => $task->getId())); ?>')" class="btn btn-default btn-sm btn-nospin" data-toggle="ajaxmodal"><span data-toggle="tooltip" title="" data-placement="left" data-original-title=""><i class="fa fa-check"></i> </span></a>
            </div>
        </div>
    </div>
</li>