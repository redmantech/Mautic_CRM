<?php

namespace MauticPlugin\CustomCrmBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class Task
{
    protected $id;

    protected $isCompleted = 0;

    protected $name;

    protected $dueDate;

    protected $lead;

    protected $assignUser;

    protected $dateAdded;

    protected $dateCompleted;

    public function __construct()
    {
        $this->dateAdded = new \DateTime('now');
    }

    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ORM\Builder\ClassMetadataBuilder($metadata);

        $builder->setTable('tasks');

        $builder->createField('id', 'integer')
            ->isPrimaryKey()
            ->generatedValue()
            ->build();

        $builder->createField('name', 'string')
            ->columnName('name')
            ->build();

        $builder->createField('dueDate', 'datetime')
            ->columnName('due_date')
            ->build();

        $builder->createField('isCompleted', 'boolean')
            ->columnName('is_completed')
            ->build();

        $builder->createField('dateAdded', 'datetime')
            ->columnName('date_added')
            ->build();

        $builder->createField('dateCompleted', 'datetime')
            ->columnName('date_completed')
            ->nullable()
            ->build();

        $builder->createManyToOne('lead', 'Mautic\LeadBundle\Entity\Lead')
            ->addJoinColumn('lead_id', 'id', true, false, 'CASCADE')
            ->build();

        $builder->createManyToOne('assignUser', 'Mautic\UserBundle\Entity\User')
            ->addJoinColumn('assign_user_id', 'id', true, false, 'CASCADE')
            ->build();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param mixed $dueDate
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param mixed $lead
     */
    public function setLead($lead)
    {
        $this->lead = $lead;
    }

    /**
     * @return mixed
     */
    public function getAssignUser()
    {
        return $this->assignUser;
    }

    /**
     * @param mixed $assignUser
     */
    public function setAssignUser($assignUser)
    {
        $this->assignUser = $assignUser;
    }

    /**
     * @return mixed
     */
    public function getIsCompleted()
    {
        return $this->isCompleted;
    }

    /**
     * @param mixed $isCompleted
     */
    public function setIsCompleted($isCompleted)
    {
        $this->isCompleted = $isCompleted;
    }

    /**
     * @return mixed
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @param mixed $dateAdded
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
    }

    /**
     * @return mixed
     */
    public function getDateCompleted()
    {
        return $this->dateCompleted;
    }

    /**
     * @param mixed $dateCompleted
     */
    public function setDateCompleted($dateCompleted)
    {
        $this->dateCompleted = $dateCompleted;
    }

}