<?php

namespace MauticPlugin\CustomCrmBundle\Form\Type;

use Mautic\CoreBundle\Model\CommonModel;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class LeadToIdTransformer implements DataTransformerInterface
{
    /** @var CommonModel */
    private $model;

    public function __construct(CommonModel $model)
    {
        $this->model = $model;
    }

    public function transform($lead)
    {
        if (null === $lead) {
            return '';
        }

        return $lead->getId();
    }

    public function reverseTransform($value)
    {
        if (!$value) {
            return;
        }

        $lead = $this->model->getEntity($value);

        if (null === $value) {
            throw new TransformationFailedException(sprintf(
                'An Lead with id "%s" does not exist!',
                $value
            ));
        }

        return $lead;
    }
}