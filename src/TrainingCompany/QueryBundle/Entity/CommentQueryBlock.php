<?php
namespace TrainingCompany\QueryBundle\Entity;

use TrainingCompany\QueryBundle\Entity\Doctrine\QComments;
use TrainingCompany\QueryBundle\Entity\Configuration;

class CommentQueryBlock extends QueryBlock {

    public $label;
    public $comment;

    public function __construct($id, $label, $qno) {
        $this->id = $id;
        $this->qno = $qno;
        $this->blocktype = 'COMMENT';
        $this->label = $label;
    }

    public function getBlockId() {
        return 'comment_'.$this->id;
    }
    
    public function get($em, $pid, $qid, $qno) {
        $qcomments = $em->getRepository(Configuration::CommentRepo())
                        ->findOneBy(array('pid' => $pid, 'qid' => $qid, 'qno' => $this->qno));
        if (!$qcomments) {
            $this->comment = '';
        }
        else {
            $this->comment = $qcomments->getComment();
        }
    }

    public function persist($em, $pid, $qid, $qno) {
        $qcomments = $em->getRepository(Configuration::CommentRepo())
                        ->findOneBy(array('pid' => $pid, 'qid' => $qid, 'qno' => $this->qno));
        $new = !$qcomments;
        if ($new) {
            $qcomments = new QComments();
            $qcomments->setPid($pid);
            $qcomments->setQid($qid);
            $qcomments->setQno($this->qno);
        }
        $qcomments->setComment($this->comment);
        if ($new) {
            $em->persist($qcomments);
        }
    }

    public function readForm($formData) {
        $this->comment = $formData->{$this->getBlockId()};
    }

    public function populateForm($formData, $formDef) {
        $formData->{$this->getBlockId()} = $this->comment;
        $formDef->add($this->getBlockId(), 'textarea', array('label' => html_entity_decode($this->label, ENT_NOQUOTES, 'UTF-8'), 'required' => false));
    }
}