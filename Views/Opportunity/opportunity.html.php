<?php

if ($opportunity instanceof \MauticPlugin\CustomCrmBundle\Entity\Opportunity) {
    $id        = $opportunity->getId();
    $value     = $opportunity->getValue();
    $valueType = \MauticPlugin\CustomCrmBundle\Entity\Opportunity::getValueTypeLabels($opportunity->getValueType());
    $valueType = $view['translator']->trans($valueType);
    $status    = \MauticPlugin\CustomCrmBundle\Entity\Opportunity::getStatusLabels($opportunity->getStatus());
    $status    = $view['translator']->trans($status);
    $date      = $opportunity->getDateAdded();
    $owner     = $opportunity->getOwnerUser();
    $comments  = $opportunity->getComments();
} else {
    $id        = $opportunity['id'];
    $value     = $opportunity['value'];
    $valueType = \MauticPlugin\CustomCrmBundle\Entity\Opportunity::getValueTypeLabels($opportunity['valueType']);
    $valueType = $view['translator']->trans($valueType);
    $status    = \MauticPlugin\CustomCrmBundle\Entity\Opportunity::getStatusLabels($opportunity['status']);
    $status    = $view['translator']->trans($status);
    $date      = $opportunity['date_added'];
    $owner     = $opportunity['owner'];
    $comments  = $opportunity['comments'];
}
?>


<li id="Opportunity<?php echo $id; ?>">
    <div class="panel ">
        <div class="panel-body np box-layout">
            <div class="height-auto icon bdr-r bg-dark-xs col-xs-1 text-center">
                <h3><i class="fa fa-lg fa-fw fa-dollar"></i></h3>
            </div>
            <div class="media-body col-xs-11 pa-10">
                <div class="pull-right btn-group">
                    <a class="btn btn-default btn-xs"
                       href="<?php echo $this->container->get('router')->generate('mautic_customcrm_opportunity_action', array('objectAction' => 'quickAdd', 'objectId' => $id)); ?>"
                       data-toggle="ajaxmodal" data-target="#MauticSharedModal"
                       data-header="<?php echo $view['translator']->trans('mautic.customcrm.opportunity.header.edit'); ?>"><i
                            class="fa fa-pencil"></i></a>

                    <a class="btn btn-default btn-xs"
                       data-toggle="confirmation"
                       href="<?php echo $view['router']->generate('mautic_customcrm_opportunity_action', array('objectAction' => 'delete', 'objectId' => $id, 'qf' => 1)); ?>"
                       data-message="<?php echo $view->escape($view["translator"]->trans('customcrm.mautic.opportunity.confirmdelete')); ?>"
                       data-confirm-text="<?php echo $view->escape($view["translator"]->trans("mautic.core.form.delete")); ?>"
                       data-confirm-callback="executeAction"
                       data-cancel-text="<?php echo $view->escape($view["translator"]->trans("mautic.core.form.cancel")); ?>">
                        <i class="fa fa-trash text-danger"></i>
                    </a>
                </div>
                <b><?php echo $valueType; ?> <?php echo '$' . $value; ?> - <?php echo $status ?></b>
                <br/>
                <?php echo $comments ?>
                <div class="mt-15 text-muted">
                    <i class="fa fa-clock-o fa-fw"></i>
                    <span class="small"><?php echo $date->format('m-d-Y'); ?></span>

                    <i class="fa fa-user fa-fw"></i>
                    <?php if ($owner instanceof \Mautic\UserBundle\Entity\User): ?>
                    <span class="small"><?php echo $owner->getName(); ?></span>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</li>
