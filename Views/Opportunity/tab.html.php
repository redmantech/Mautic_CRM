<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
<div class="box-layout mb-lg">
    <div class="col-xs-10 va-m">

    </div>
    <div class="col-xs-2 va-t">
        <a class="btn btn-primary btn-leadnote-add pull-right" href="<?php echo $view['router']->generate('mautic_customcrm_opportunity_action', array('leadId' => $lead->getId(), 'objectAction' => 'quickAdd')); ?>" data-toggle="ajaxmodal" data-target="#MauticSharedModal" data-header="<?php echo $view['translator']->trans('mautic.customcrm.opportunity.header.new'); ?>"><i class="fa fa-plus fa-lg"></i> <?php echo $view['translator']->trans('mautic.customcrm.opportunity.button.add'); ?></a>
    </div>
</div>

<div id="OpportunityList">
    <ul class="opportunities" id="Opportunities">
        <?php foreach ($opportunities as $opportunity): ?>
            <?php
            //Use a separate layout for AJAX generated content
            echo $view->render('CustomCrmBundle:Opportunity:opportunity.html.php', array(
                'opportunity' => $opportunity,
            )); ?>
        <?php endforeach; ?>
    </ul>
</div>
