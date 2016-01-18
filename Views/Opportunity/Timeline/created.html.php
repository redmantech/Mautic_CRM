<li class="lead-identified wrapper">
    <div class="figure"><span class="fa fa-lightbulb-o"></span></div>
    <div class="panel bg-default">
        <div class="panel-body">
            <p class="mb-0">
                <?php echo $view['translator']->trans('mautic.customcrm.opportunity.timeline.created',
                    array(
                        '%date%' => $view['date']->toFullConcat($event['timestamp']),
                        '%opportunity%' => $event['extra']['opportunity']));
                ?>
            </p>
        </div>
    </div>
</li>