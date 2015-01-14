<?php
namespace TrainingCompany\QueryBundle\Entity;

use TrainingCompany\QueryBundle\Entity\Doctrine\QComments;
use TrainingCompany\QueryBundle\Entity\Configuration;

class TextQueryBlock extends QueryBlock {

    public $label;
    public $text;

    public function __construct($id, $label) {
        $this->id = $id;
        $this->blocktype = 'TEXT';
        $this->label = $label;
    }

    public function getBlockId() {
        return 'text_'.$this->id;
    }
    
    public function get($em, $pid, $qid, $qno) {
        $qcomments = $em->getRepository(Configuration::CommentRepo())
                        ->findOneBy(array('pid' => $pid, 'qid' => $qid, 'qno' => $this->id));
        if (!$qcomments) {
            $this->text = '';
        }
        else {
            $this->text = $qcomments->getComment();
        }
    }

    public function persist($em, $pid, $qid, $qno) {
        $qcomments = $em->getRepository(Configuration::CommentRepo())
                        ->findOneBy(array('pid' => $pid, 'qid' => $qid, 'qno' => $this->id));
        $new = !$qcomments;
        if ($new) {
            $qcomments = new QComments();
            $qcomments->setPid($pid);
            $qcomments->setQid($qid);
            $qcomments->setQno($this->id);
        }
        $qcomments->setComment($this->text);
        if ($new) {
            $em->persist($qcomments);
        }
    }

    public function readForm($formData) {
        $this->text = $formData->{$this->getBlockId()};
    }

    public function populateForm($formData, $formDef) {
        $formData->{$this->getBlockId()} = $this->text;
        $formDef->add($this->getBlockId(), 'text', array('label' => html_entity_decode($this->label, ENT_NOQUOTES, 'UTF-8'), 'required' => false));
    }
}