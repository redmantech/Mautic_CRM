<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
//Check to see if the entire page should be displayed or just main content
if ($tmpl == 'index'):
    $view->extend('CustomCrmBundle:Opportunity:index.html.php');
endif;
$listCommand = $view['translator']->trans('customcrm.opportunity.searchcommand.list');
?>

<?php if (count($items)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered" id="opportunityListTable">
            <thead>
            <tr>
                <?php
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', array(
                    'checkall' => 'true',
                    'target'   => '#opportunityListTable'
                ));
                ?>
                <th class="visible-md visible-lg"><?php echo $view['translator']->trans('customcrm.opportunity.lead'); ?></th>
                <th class="visible-md visible-lg"><?php echo $view['translator']->trans('customcrm.opportunity.value'); ?></th>
                <th class="visible-md visible-lg"><?php echo $view['translator']->trans('customcrm.opportunity.confidence'); ?></th>
                <th class="visible-md visible-lg"><?php echo $view['translator']->trans('customcrm.opportunity.close_date'); ?></th>
                <th class="visible-md visible-lg"><?php echo $view['translator']->trans('customcrm.opportunity.status'); ?></th>
                <th class="visible-md visible-lg"><?php echo $view['translator']->trans('customcrm.opportunity.user'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item):?>
                <?php /** @var $item \MauticPlugin\CustomCrmBundle\Entity\Opportunity */ ?>
                <tr>
                    <td class="visible-md visible-lg">
                        <?php
                        echo $view->render('MauticCoreBundle:Helper:list_actions.html.php', array(
                            'item'      => $item,
                            'templateButtons' => array(
                                'edit' => true,
                                'delete'    => true,
                            ),
                            'routeBase' => 'customcrm_opportunity',
                            'langVar'   => 'customcrm.opportunity'
                        ));
                        ?>
                    </td>
                    <td class="visible-md visible-lg">
                        <?php if ($item->getLead()): ?>
                            <a href="<?php echo $view['router']->generate('mautic_lead_action', array(
                                        'objectAction' => 'view',
                                        'objectId' => $item->getLead()->getId()
                                    )); ?>" alt="<?php echo $item->getLead()->getName() ?>">
                                <?php echo $item->getLead()->getName() ?>
                            </a>
                        <?php endif ?>
                        <br/>
                        <?php if ($item->getComments()): ?>
                            <small>(<?php echo $item->getComments() ?>)</small>
                        <?php endif ?>
                    </td>
                    <td class="visible-md visible-lg">
                        <?php echo '$' . number_format($item->getValue()) ?>
                    </td>
                    <td class="visible-md visible-lg">
                        <?php echo $item->getConfidence() . '%' ?>
                    </td>
                    <td class="visible-md visible-lg">
                        <?php if ($item->getEstimatedClose() instanceof \DateTime): ?>
                            <?php echo $item->getEstimatedClose()->format('m/d/Y') ?>
                        <?php endif; ?>
                    </td>
                    <td class="visible-md visible-lg">
                        <?php $label = \MauticPlugin\CustomCrmBundle\Entity\Opportunity::getStatusLabels($item->getStatus()); ?>
                        <?php echo $view['translator']->trans($label) ?>
                    </td>
                    <td class="visible-md visible-lg">
                        <?php if ($item->getOwnerUser()): ?>
                            <?php echo $item->getOwnerUser()->getName() ?>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="panel-footer">
            <?php echo $view->render('MauticCoreBundle:Helper:pagination.html.php', array(
                "totalItems" => count($items),
                "page"       => $page,
                "limit"      => $limit,
                "baseUrl"    =>  $view['router']->generate('mautic_customcrm_opportunity_index'),
                'sessionVar' => 'opportunitylist'
            )); ?>
        </div>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>
