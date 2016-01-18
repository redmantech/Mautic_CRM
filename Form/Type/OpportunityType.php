<?php

namespace MauticPlugin\CustomCrmBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use MauticPlugin\CustomCrmBundle\Entity\Opportunity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OpportunityType extends AbstractType
{
    protected $factory;

    public function __construct(MauticFactory $factory)
    {
        $this->factory = $factory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', 'choice', array(
                'choices' => Opportunity::getStatusLabels(),
                'label'      => 'mautic.customcrm.opportunity.form.status',
                'label_attr' => array('class' => 'control-label'),
                'attr'       => array(
                    'tooltip'              => 'mautic.customcrm.opportunity.form.status.help',
                    'class'                => 'nomousetrap form-control',
                ),
            ))
            ->add('confidence', 'number', array(
                'label'      => 'mautic.customcrm.opportunity.confidence',
                'label_attr' => array('class' => 'control-label'),
                'attr'       => array(
                    'class'    => 'nomousetrap form-control',
                    'tooltip'  => 'mautic.customcrm.opportunity.form.confidence.help'
                ),
            ))
            ->add('value', 'number', array(
                'label'      => 'mautic.customcrm.opportunity.form.value',
                'label_attr' => array('class' => 'control-label'),
                'attr'       => array(
                    'class'    => 'nomousetrap form-control',
                    'preaddon' => 'fa fa-dollar',
                    'tooltip'  => 'mautic.customcrm.opportunity.form.value.help'
                ),
            ))
            ->add('valueType', 'choice', array(
                'choices' => Opportunity::getValueTypeLabels(),
                'label'      => 'mautic.customcrm.opportunity.form.value_type',
                'label_attr' => array('class' => 'control-label'),
                'attr'       => array(
                    'tooltip'              => 'mautic.customcrm.opportunity.form.value_type.help',
                    'class'                => 'nomousetrap form-control',
                ),
            ))->add('estimatedClose', 'date', array(
                'widget' => 'single_text',
                'label'      => 'mautic.customcrm.opportunity.form.estimated_close',
                'label_attr' => array('class' => 'control-label'),
                'attr'       => array(
                    'tooltip'              => 'mautic.customcrm.opportunity.form.estimated_close.help',
                    'class'                => 'nomousetrap form-control',
                    'data-toggle' => 'date'
                ),
                'format'     => 'yyyy-MM-dd'
            ));

        if (empty($options['isShortForm'])) {
            $builder->add('lead', 'entity', array(
                'class' => 'MauticLeadBundle:Lead',
                'property' => 'name',
                'choices' => $this->factory->getModel('lead.lead')->getEntities(),
                'label'      => 'mautic.customcrm.opportunity.form.lead',
                'label_attr' => array('class' => 'nomousetrap control-label'),
                'attr'       => array(
                    'tooltip'              => 'mautic.customcrm.opportunity.form.lead.help',
                    'class'                => 'form-control' . (empty($options['leadId']) ?: ' hidden')
                ),
            ));
        }

        $builder
            ->add('comments', 'textarea', array(
                'label'      => 'mautic.customcrm.opportunity.form.comments',
                'label_attr' => array('class' => 'control-label'),
                'attr'       => array(
                    'tooltip'              => 'mautic.customcrm.opportunity.form.comments.help',
                    'class'                => 'nomousetrap form-control',
                    'rows'                 => '8',
                ),
                'required'   => false
            ))
        ;

        if (empty($options['isShortForm'])) {
            $builder->add('buttons', 'form_buttons');
        } else {
            $builder->add(
                'buttons',
                'form_buttons',
                array(
                    'apply_text' => false,
                    'save_text'  => 'mautic.core.form.save'
                )
            );
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array(
            'isShortForm'
        ));

        $resolver->setDefaults(array(
            'data_class' => 'MauticPlugin\CustomCrmBundle\Entity\Opportunity'
        ));
    }

    public function getName()
    {
        return 'customcrm_opportunity';
    }
}