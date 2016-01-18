<?php

$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('mauticContent', 'opportunity');

$headerTitle = $entity->isNew() ? 'customcrm.opportunity.new_opportunity' : 'customcrm.opportunity.edit_opportunity';

$view['slots']->set('headerTitle', $view['translator']->trans($headerTitle));
?>

<?php echo $view['form']->start($form); ?>
<div class="box-layout">
    <!-- container -->
    <div class="col-md-12 bg-auto height-auto">
        <div class="pa-md">
            <div class="row">
                <div class="col-md-3">
                    <?php echo $view['form']->row($form['lead']); ?>
                </div>

                <div class="col-md-3">
                    <?php echo $view['form']->row($form['valueType']); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <?php echo $view['form']->row($form['status']); ?>
                </div>

                <div class="col-md-3">
                    <?php echo $view['form']->row($form['confidence']); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <?php echo $view['form']->row($form['estimatedClose']); ?>
                </div>

                <div class="col-md-3">
                    <?php echo $view['form']->row($form['value']); ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?php echo $view['form']->row($form['comments']); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $view['form']->end($form); ?>