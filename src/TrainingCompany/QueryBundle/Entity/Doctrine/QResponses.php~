<?php

namespace TrainingCompany\QueryBundle\Entity\Doctrine;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrainingCompany\QueryBundle\Entity\Doctrine\QResponses
 *
 * @ORM\Table(name="q_responses")
 * @ORM\Entity
 */
class QResponses
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
     * @var integer $pid
     *
     * @ORM\Column(name="pid", type="integer", nullable=false)
     */
    private $pid;

    /**
     * @var integer $qid
     *
     * @ORM\Column(name="qid", type="integer", nullable=false)
     */
    private $qid;

    /**
     * @var integer $qno
     *
     * @ORM\Column(name="qno", type="integer", nullable=false)
     */
    private $qno;

    /**
     * @var string $answer
     *
     * @ORM\Column(name="answer", type="string", length=5, nullable=true)
     */
    private $answer;



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
     * @return QResponses
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
     * Set qid
     *
     * @param integer $qid
     * @return QResponses
     */
    public function setQid($qid)
    {
        $this->qid = $qid;
    
        return $this;
    }

    /**
     * Get qid
     *
     * @return integer 
     */
    public function getQid()
    {
        return $this->qid;
    }

    /**
     * Set qno
     *
     * @param integer $qno
     * @return QResponses
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
     * Set answer
     *
     * @param string $answer
     * @return QResponses
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
    
        return $this;
    }

    /**
     * Get answer
     *
     * @return string 
     */
    public function getAnswer()
    {
        return $this->answer;
    }
}