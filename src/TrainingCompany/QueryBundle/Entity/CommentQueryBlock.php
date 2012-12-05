<?php
namespace TrainingCompany\QueryBundle\Entity;

use TrainingCompany\QueryBundle\Entity\Doctrine\QComments;

class CommentQueryBlock extends QueryBlock {

    public $label;
    public $comment;

    private $repositoryPath = 'TrainingCompany\QueryBundle\Entity\Doctrine\QComments';

    public function __construct() {
            $this->blocktype = 'COMMENT';
    }

    public function get($em, $pid, $qid, $qno) {
        $qcomments = $em->getRepository($this->repositoryPath)->findOneBy(array('pid' => $pid, 'qid' => $qid, 'qno' => $qno));
        if (!$qcomments)
            $this->comment = '';
        else
            $this->comment = $qcomments->getComment();
    }

    public function persist($em, $pid, $qid, $qno) {
        $qcomments = $em->getRepository($this->repositoryPath)->findOneBy(array('pid' => $pid, 'qid' => $qid, 'qno' => $qno));
        $new = !$qcomments;
        if ($new) {
            $qcomments = new QComments();
            $qcomments->setPid($pid);
            $qcomments->setQid($qid);
            $qcomments->setQno($qno);
        }
        $qcomments->setComment($this->comment);
        if ($new) $em->persist($qcomments);
    }

    public function readForm($formData) {
        $this->comment = $formData->comment;
    }

    public function populateForm($formData, $formDef) {
        $formData->comment = $this->comment;

        $formDef->add('comment', 'textarea', array('label' => html_entity_decode($this->label, ENT_NOQUOTES, 'UTF-8'), 'required' => false));
    }
}