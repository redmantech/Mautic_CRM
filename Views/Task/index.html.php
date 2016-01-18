<?php

$view->extend('MauticCoreBundle:Default:content.html.php');

$view['slots']->set('mauticContent', 'task');
$view['slots']->set('headerTitle', $view['translator']->trans('ddi.lead_actions.tasks'));

/*$view['slots']->set('actions', $view->render('MauticCoreBundle:Helper:page_actions.html.php', array(
    'templateButtons' => array(
        'new' => 'foo'
    ),
    'routeBase' => 'lead',
    'langVar'   => 'lead.lead',
)));*/

$routeBase = 'task';
$langVar = 'task.task';
$templateButtons = array(
    'delete' => true
);

?>

<script src="/plugins/CustomCrmBundle/Assets/js/leadactions.js"></script>

<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <div class="panel-body">
        <div class="box-layout">
            <div class="col-xs-2 col-lg-2 va-m form-inline">
                <?php echo $view->render('MauticCoreBundle:Helper:search.html.php', array(
                    'searchId'    => (empty($searchId)) ? null : $searchId,
                    'searchValue' => $searchValue,
                    'action'      => $currentRoute,
                    'searchHelp'  => (isset($searchHelp)) ? $searchHelp : '',
                    'target'      => (empty($target)) ? null : $target,
                    'tmpl'        => null
                )); ?>
            </div>

            <div class="col-xs-2 col-lg-2">
                <div class="input-group">
                    <span class="input-group-addon preaddon">
                    <i class="fa fa-calendar"></i>
                    </span>
                    <input autocomplete="false" type="text" id="dueDate_filter" class="form-control calendar-activated" data-toggle="datetime" data-action="<?php echo $currentRoute ?>" value="<?php echo $dueDateFilter; ?>">
                    <div class="input-group-btn">
                        <button type="button" class="btn btn-default btn-nospin" id="btn-dueDate-filter" data-action="<?php echo $currentRoute ?>">
                            <i class="fa fa-fw fa-eraser"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-xs-6 col-lg-4 va-m text-right">
                <a id="btn-ownerMe-filter" class="btn btn-default btn-sm btn-nospin <?php echo $ownerMe ? 'btn-primary' : ''; ?>" data-href="<?php echo $view['router']->generate('ddi_lead_actions_task_index') . '?owner=' . ($ownerMe ? '' : 'me'); ?>"><span data-toggle="tooltip" title="" data-placement="left" data-original-title="<?php echo $view['translator']->trans('ddi.lead_actions.tasks.filter.owner_me'); ?>"><i class="fa fa-user-secret"></i> </span></a>
                <?php //TODO - Support more buttons

                //Custom query parameters for URLs
                if (!isset($query)) {
                    $query = array();
                }

                if (isset($tmpl)) {
                    $query['tmpl'] = $tmpl;
                }

                if (!empty($templateButtons['delete'])):
                    echo $view->render('MauticCoreBundle:Helper:confirm.html.php', array(
                        'message'       => $view['translator']->trans('mautic.' . $langVar . '.form.confirmbatchdelete'),
                        'confirmAction' => $view['router']->generate('mautic_' . $routeBase . '_action', array_merge(array('objectAction' => 'batchDelete'), $query)),
                        'template'      => 'batchdelete',
                        'tooltip'       => $view['translator']->trans('mautic.core.form.tooltip.bulkdelete'),
                        'precheck'      => 'batchActionPrecheck',
                        'target'        => (empty($target)) ? null : $target
                    ));
                endif;
                ?>
            </div>
        </div>
    </div>
    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>
