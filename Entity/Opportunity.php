<?php

namespace MauticPlugin\CustomCrmBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class Opportunity
 * @package MauticPlugin\CustomCrmBundle
 */
class Opportunity extends FormEntity
{
    const STATUS_ACTIVE = 'active';
    const STATUS_ON_HOLD = 'on-hold';
    const STATUS_WON = 'won';
    const STATUS_LOST = 'lost';

    const VALUE_TYPE_ONE_TIME = 'one-time';
    const VALUE_TYPE_MONTHLY = 'monthly';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $status;

    /**
     * @var integer
     */
    private $confidence;

    /**
     * @var integer
     */
    private $value;

    /**
     * @var integer
     */
    private $valueType;

    /**
     * @var \DateTime
     */
    private $estimatedClose;

    /**
     * @var \Mautic\LeadBundle\Entity\Lead
     */
    private $lead;

    /**
     * @var \Mautic\UserBundle\Entity\User
     */
    private $ownerUser;

    /**
     * @var string
     */
    private $comments;

    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('opportunities')
            ->setCustomRepositoryClass('MauticPlugin\CustomCrmBundle\Entity\OpportunityRepository');

        $builder->createField('id', 'integer')
            ->isPrimaryKey()
            ->generatedValue()
            ->build();

        $builder->createField('status', 'string')
            ->length(32)
            ->build();

        $builder->createField('confidence', 'integer')
            ->nullable()
            ->build();

        $builder->createField('value', 'integer')
            ->nullable()
            ->build();

        $builder->createField('valueType', 'string')
            ->length(32)
            ->columnName('value_type')
            ->nullable()
            ->build();

        $builder->createField('estimatedClose', 'date')
            ->columnName('estimated_close')
            ->nullable()
            ->build();

        $builder->createField('comments', 'text')
            ->nullable()
            ->build();

        $builder->createManyToOne('lead', 'Mautic\LeadBundle\Entity\Lead')
            ->addJoinColumn('lead_id', 'id', false, false)
            ->cascadePersist()
            ->build();

        $builder->createManyToOne('ownerUser', 'Mautic\UserBundle\Entity\User')
            ->addJoinColumn('owner_user_id', 'id', true, false)
            ->build();
    }

    /**
     * Form validation rules
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata (ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('status', new Assert\NotBlank());

        $metadata->addPropertyConstraint('confidence', new Assert\NotBlank());

        $metadata->addPropertyConstraint('confidence', new Assert\Range(array('min' => 0, 'max' => 100)));

        $metadata->addPropertyConstraint('value', new NotBlank());
        $metadata->addPropertyConstraint('valueType', new NotBlank());
        $metadata->addPropertyConstraint('estimatedClose', new NotBlank());
    }

    public function __construct()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->valueType = self::VALUE_TYPE_ONE_TIME;
    }

    public function getName()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public static function getStatusLabels($statusId = null)
    {
        if ($statusId) {
            $statusArray = self::getStatusLabels();
            return array_key_exists($statusId, $statusArray) ? $statusArray[$statusId] : 'customcrm.opportunity.unknown';
        }

        return array(
            self::STATUS_ACTIVE => 'customcrm.opportunity.status.active',
            self::STATUS_ON_HOLD => 'customcrm.opportunity.status.on_hold',
            self::STATUS_WON => 'customcrm.opportunity.status.won',
            self::STATUS_LOST => 'customcrm.opportunity.status.lost'
        );
    }

    /**
     * @return int
     */
    public function getConfidence()
    {
        return $this->confidence;
    }

    /**
     * @param int $confidence
     */
    public function setConfidence($confidence)
    {
        $this->confidence = $confidence;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return \DateTime
     */
    public function getValueType()
    {
        return $this->valueType;
    }

    /**
     * @param \DateTime $valueType
     */
    public function setValueType($valueType)
    {
        $this->valueType = $valueType;
    }

    public static function getValueTypeLabels($valueTypeId = null)
    {
        if ($valueTypeId) {
            $valueTypeLabels = self::getValueTypeLabels();
            return array_key_exists($valueTypeId, $valueTypeLabels) ? $valueTypeLabels[$valueTypeId] : 'customcrm.opportunity.unknown';
        }

        return array(
            self::VALUE_TYPE_ONE_TIME => 'customcrm.opportunity.value_type.one_time',
            self::VALUE_TYPE_MONTHLY => 'customcrm.opportunity.value_type.monthly'
        );
    }

    /**
     * @return int
     */
    public function getEstimatedClose()
    {
        return $this->estimatedClose;
    }

    /**
     * @param \DateTime $estimatedClose
     */
    public function setEstimatedClose($estimatedClose)
    {
        $this->estimatedClose = $estimatedClose;
    }

    /**
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \Mautic\LeadBundle\Entity\Lead
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param \Mautic\LeadBundle\Entity\Lead $lead
     */
    public function setLead($lead)
    {
        $this->lead = $lead;
    }

    /**
     * @return \Mautic\UserBundle\Entity\User
     */
    public function getOwnerUser()
    {
        return $this->ownerUser;
    }

    /**
     * @param \Mautic\UserBundle\Entity\User $ownerUser
     */
    public function setOwnerUser($ownerUser)
    {
        $this->ownerUser = $ownerUser;
    }
}
