<?php

namespace TrainingCompany\QueryBundle\Entity\Doctrine;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrainingCompany\QueryBundle\Entity\Doctrine\QSurveys
 *
 * @ORM\Table(name="q_surveys")
 * @ORM\Entity
 */
class QSurveys {

    static public $STATE_INVITED = 0;
    static public $STATE_ONGOING = 1;
    static public $STATE_FINISHED = 2;
    static public $STATE_OPTED = 3;
    
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Person ref
     * @var integer $pid
     *
     * @ORM\Column(name="pid", type="integer", nullable=false)
     */
    private $pid;

    /**
     * Schema ref
     * @var integer $sid
     *
     * @ORM\Column(name="sid", type="integer", nullable=false)
     */
    private $sid;

    /**
     * Questionaire state
     * @var integer $state
     *
     * @ORM\Column(name="state", type="integer", nullable=false)
     */
    private $state;

    /**
     * Date of invitation
     * @var string $date
     *
     * @ORM\Column(name="date", type="string", nullable=true)
     */
    private $date;

    /**
     * Question no
     * @var integer $qno
     *
     * @ORM\Column(name="qno", type="integer", nullable=false)
     */
    private $qno;

    /**
     * @var string $token
     *
     * @ORM\Column(name="token", type="string", length=36, nullable=true)
     */
    private $token;



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
     * Set pid
     *
     * @param integer $pid
     * @return QSurveys
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    
        return $this;
    }

    /**
     * Get pid
     *
     * @return integer 
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Set questionaire schema id
     *
     * @param integer $sid
     * @return QSurveys
     */
    public function setSid($sid)
    {
        $this->sid = $sid;
    
        return $this;
    }

    /**
     * Get questionaire schema id
     *
     * @return integer 
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set questionaire state
     *
     * @param string $state
     * @return QSurveys
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get questionaire state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set qno
     *
     * @param integer $qno
     * @return QSurveys
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
     * Set date
     *
     * @param string $date
     * @return QSurveys
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return QSurveys
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}