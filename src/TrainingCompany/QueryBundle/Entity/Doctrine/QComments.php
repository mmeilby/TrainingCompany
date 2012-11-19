<?php

namespace TrainingCompany\QueryBundle\Entity\Doctrine;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrainingCompany\QueryBundle\Entity\Doctrine\QComments
 *
 * @ORM\Table(name="q_comments")
 * @ORM\Entity
 */
class QComments
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
     * @var string $comment
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;



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
     * @return QComments
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
     * @return QComments
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
     * @return QComments
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
     * Set comment
     *
     * @param string $comment
     * @return QComments
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    
        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }
}