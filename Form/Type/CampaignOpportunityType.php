<?php

namespace MauticPlugin\CustomCrmBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Form\DataTransformer\DatetimeToStringTransformer;
use Mautic\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CampaignOpportunityType extends OpportunityType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('estimatedClose');
        $builder->remove('lead');
        $builder->remove('buttons');

        $data = (empty($options['data']['dateInterval'])) ? 1 : $options['data']['dateInterval'];
        $builder->add('dateInterval', 'number', array(
            'label'      => 'mautic.customcrm.opportunity.form.estimated_close_after',
            'attr'       => array(
                'class'    => 'form-control',
                'preaddon' => 'symbol-hashtag'
            ),
            'data'       => $data
        ));

        $data = (!empty($options['data']['dateIntervalUnit'])) ? $options['data']['dateIntervalUnit'] : 'd';
        $builder->add('dateIntervalUnit', 'choice', array(
            'choices'     => array(
                'i' => 'mautic.campaign.event.intervalunit.choice.i',
                'h' => 'mautic.campaign.event.intervalunit.choice.h',
                'd' => 'mautic.campaign.event.intervalunit.choice.d',
                'm' => 'mautic.campaign.event.intervalunit.choice.m',
                'y' => 'mautic.campaign.event.intervalunit.choice.y',
            ),
            'multiple'    => false,
            'label_attr'  => array('class' => 'control-label'),
            'label'       => false,
            'attr'        => array(
                'class' => 'form-control'
            ),
            'empty_value' => false,
            'required'    => false,
            'data'        => $data
        ));

        $builder->add('ownerUser', 'user_list', array(
            'label'      => 'mautic.customcrm.opportunity.form.owner',
            'label_attr' => array('class' => 'control-label, required'),
            'attr'       => array(
                'tooltip'  => 'mautic.customcrm.opportunity.form.user.help',
                'class' => 'form-control'
            ),
            'required'   => true,
            'multiple'   => false,
            'constraints' => array(
                new NotBlank(
                    array('message' => 'Owner is required')
                )
            )
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array(
            'leadId', // optionally provide the lead id for the quick add form
            'isShortForm'
        ));

        $resolver->setDefaults(array(
            'data_class' => null
        ));
    }

    public function getName()
    {
        return 'customcrm_campaign_opportunity';
    }
}