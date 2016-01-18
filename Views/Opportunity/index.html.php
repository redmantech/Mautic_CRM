<?php

$view->extend('MauticCoreBundle:Default:content.html.php');


$view['slots']->set('mauticContent', 'opportunity');
$view['slots']->set("headerTitle", $view['translator']->trans('customcrm.opportunities'));

$view['slots']->set('actions', $view->render('MauticCoreBundle:Helper:page_actions.html.php', array(
    'templateButtons' => array(
        'new' => true
    ),
    'routeBase' => 'customcrm_opportunity',
    'langVar'   => 'opportunity.list',
)));
?>

<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <?php echo $view->render('MauticCoreBundle:Helper:list_toolbar.html.php', array(
        'action'      => $currentRoute,
        'langVar'     => 'opportunity.list',
        'routeBase'   => 'customcrm_opportunity',
        'templateButtons' => array(
            'delete' => true
        ),
        'filters'     => $filters
    )); ?>



    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>