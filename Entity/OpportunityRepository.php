<?php

namespace MauticPlugin\CustomCrmBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

class OpportunityRepository extends CommonRepository
{
    public function getOpportunityCount($opportunityIds)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();

        $q->select('count(o.id) as thecount, o.id')
            ->from(MAUTIC_TABLE_PREFIX.'opportunities', 'o');

        $returnArray = is_array($opportunityIds);

        if (!$returnArray) {
            $opportunityIds = array($opportunityIds);
        }

        return $q->where('o.id IN (:o_ids)')
            ->setParameter('o_id', $opportunityIds)
            ->getFirstResult();
    }

    public function getEntities($args = array())
    {
        $q = $this->_em
            ->createQueryBuilder()
            ->select('e')
            ->from('CustomCrmBundle:Opportunity', 'e', 'e.id')
            ->leftJoin('e.ownerUser', 'o');

        $args['qb'] = $q;

        return parent::getEntities($args);
    }
}
