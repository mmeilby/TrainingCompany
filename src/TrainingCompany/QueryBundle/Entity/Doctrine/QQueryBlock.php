<?php

namespace TrainingCompany\QueryBundle\Entity\Doctrine;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrainingCompany\QueryBundle\Entity\Doctrine\QQueryBlock
 *
 * @ORM\Table(name="q_queryblock")
 * @ORM\Entity
 */
class QQueryBlock
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
     * @var integer $sid
     *
     * @ORM\Column(name="sid", type="integer", nullable=false)
     */
    private $sid;

    /**
     * @var integer $qno
     *
     * @ORM\Column(name="qno", type="integer", nullable=false)
     */
    private $qno;

    /**
     * @var integer $qpage
     *
     * @ORM\Column(name="qpage", type="integer", nullable=false)
     */
    private $qpage;

    /**
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=150, nullable=true)
     */
    private $label;

    /**
     * @var integer $qtype
     *
     * @ORM\Column(name="qtype", type="integer", nullable=false)
     */
    private $qtype;

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
     * Set sid
     *
     * @param integer $sid
     * @return QQueryBlock
     */
    public function setSid($sid)
    {
        $this->sid = $sid;
    
        return $this;
    }

    /**
     * Get sid
     *
     * @return integer 
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set qno
     *
     * @param integer $qno
     * @return QQueryBlock
     */
    public function setQno($qno)
    {
        $this->qno = $qno;
    
        return $this;
    }

    /**
     * Get qno
     *
     * @return integer 
     */
    public function getQno()
    {
        return $this->qno;
    }

    /**
     * Set qpage
     *
     * @param integer $qpage
     * @return QQueryBlock
     */
    public function setQpage($qpage)
    {
        $this->qpage = $qpage;
    
        return $this;
    }

    /**
     * Get qpage
     *
     * @return integer 
     */
    public function getQpage()
    {
        return $this->qpage;
    }

    /**
     * Set label
     *
     * @param string $label
     * @return QQueryBlock
     */
    public function setLabel($label)
    {
        $this->label = $label;
    
        return $this;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set qtype
     *
     * @param integer $qtype
     * @return QQueryBlock
     */
    public function setQtype($qtype)
    {
        $this->qtype = $qtype;
    
        return $this;
    }

    /**
     * Get qtype
     *
     * @return integer 
     */
    public function getQtype()
    {
        return $this->qtype;
    }
}