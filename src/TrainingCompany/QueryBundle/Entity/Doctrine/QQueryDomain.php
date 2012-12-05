<?php

namespace TrainingCompany\QueryBundle\Entity\Doctrine;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrainingCompany\QueryBundle\Entity\Doctrine\QQueryDomain
 *
 * @ORM\Table(name="q_querydomain")
 * @ORM\Entity
 */
class QQueryDomain
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $qbid
     *
     * @ORM\Column(name="qbid", type="integer", nullable=false)
     */
    private $qbid;

    /**
     * @var string $key
     *
     * @ORM\Column(name="domain", type="string", length=50, nullable=true)
     */
    private $domain;

    /**
     * @var string $value
     *
     * @ORM\Column(name="value", type="string", length=150, nullable=true)
     */
    private $value;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set domain
     *
     * @param string $domain
     * @return QQueryDomain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    
        return $this;
    }

    /**
     * Get domain
     *
     * @return string 
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return QQueryDomain
     */
    public function setValue($value)
    {
        $this->value = $value;
    
        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set qbid
     *
     * @param integer $qbid
     * @return QQueryDomain
     */
    public function setQbid($qbid)
    {
        $this->qbid = $qbid;
    
        return $this;
    }

    /**
     * Get qbid
     *
     * @return integer 
     */
    public function getQbid()
    {
        return $this->qbid;
    }
}