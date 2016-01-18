<?php
namespace MauticPlugin\CustomCrmBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Form\DataTransformer\DatetimeToStringTransformer;
use Mautic\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class TaskType extends AbstractType
{
    protected $factory;

    public function __construct(MauticFactory $factory)
    {
        $this->factory = $factory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'textarea', array(
            'label'      => 'ddi.lead_actions.tasks.thead.name',
            'label_attr' => array('class' => 'control-label'),
            'attr'       => array('class' => 'nomousetrap form-control', 'rows' => 10),
            'constraints' => array(
                new NotBlank(
                    array('message' => 'ddi.lead_actions.task.name.notblank')
                )
            )
        ));

        // Campaign builder form
        if (!empty($options['type'])) {
            $data = (empty($options['data']['dateInterval'])) ? 1 : $options['data']['dateInterval'];
            $builder->add('dateInterval', 'number', array(
                'label'      => 'ddi.lead_actions.tasks.form.due_date_after',
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
        // Default form
        } else {
            $dueDate = $builder->create(
                'dueDate', 'datetime', array(
                    'widget'     => 'single_text',
                    'label'      => 'ddi.lead_actions.tasks.thead.due_date',
                    'label_attr' => array('class' => 'control-label'),
                    'attr'       => array(
                        'class'       => 'nomousetrap form-control',
                        'data-toggle' => 'datetime',
                        'preaddon'    => 'fa fa-calendar'
                    ),
                    'format'     => 'yyyy-MM-dd HH:mm',
                    'required'   => true,
                    'constraints' => array(
                        new NotBlank(
                            array('message' => 'ddi.lead_actions.task.due_date.notblank')
                        )
                    )
                )
            );
            $builder->add($dueDate);

            $builder->add('buttons', 'form_buttons', array(
                'apply_text' => false,
                'save_text' => 'mautic.core.form.save'
            ));
        }

        $assignUser = $builder->create(
            'assignUser',
            'user_list',
            array(
                'label'      => 'ddi.lead_actions.tasks.thead.assigned_user',
                'label_attr' => array('class' => 'control-label, required'),
                'attr'       => array(
                    'class' => 'nomousetrap form-control'
                ),
                'required'   => false,
                'multiple'   => false,
                'constraints' => array(
                    new NotBlank(
                        array('message' => 'ddi.lead_actions.task.assigned_user.notblank')
                    )
                )
            )
        );
        if (empty($options['type'])) {
            $transformer = new IdToEntityModelTransformer(
                $this->factory->getEntityManager(),
                'MauticUserBundle:User'
            );
            $assignUser->addModelTransformer($transformer);
        }
        $builder->add($assignUser);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('type'));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'task';
    }
}